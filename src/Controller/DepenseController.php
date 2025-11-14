<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Form\DepenseType;
use App\Form\DepenseSearchType;
use App\Form\ExportOptionsFormType;
use App\Repository\DepenseRepository;
use App\Service\CaisseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Dompdf\Dompdf; 
use Dompdf\Options;
#[Route('/depense')]
final class DepenseController extends AbstractController
{
        #[Route('/export', name: 'app_depense_export', methods: ['GET', 'POST'])]
    public function export(Request $request, DepenseRepository $depenseRepository): Response
    {
        $exportForm = $this->createForm(ExportOptionsFormType::class);
        $exportForm->handleRequest($request);

        if ($exportForm->isSubmitted() && $exportForm->isValid()) {
            $data = $exportForm->getData();
            $exportAll = $data['exportAll'];
            $format = $data['format'];

            $qb = $depenseRepository->createQueryBuilder('d');

            if (!$exportAll) {
                $dateMin = $data['dateMin'];
                $dateMax = $data['dateMax'];

                if ($dateMin) {
                    $qb->andWhere('d.createdAt >= :dateMin')
                       ->setParameter('dateMin', $dateMin);
                }
                if ($dateMax) {
                    $qb->andWhere('d.createdAt <= :dateMax')
                       ->setParameter('dateMax', $dateMax);
                }
            }

            $depenses = $qb->getQuery()->getResult();
            $exportData = [];
            $exportData[] = ['Date', 'Description', 'Montant', 'Catégorie', 'Créé par'];

            foreach ($depenses as $depense) {
                $exportData[] = [
                    $depense->getCreatedAt()->format('d/m/Y H:i:s'),
                    $depense->getDescription(),
                    $depense->getMontant(),
                    $depense->getCategorie() ? $depense->getCategorie()->getNom() : '',
                    $depense->getUser() ? $depense->getUser()->getEmail() : ''
                ];
            }

            switch ($format) {
                case 'csv':
                    $response = new Response();
                    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
                    $response->headers->set('Content-Disposition', 'attachment; filename="depenses.csv"');

                    $handle = fopen('php://temp', 'w+b');
                    fwrite($handle, "\xEF\xBB\xBF"); // BOM for Excel UTF-8

                    foreach ($exportData as $row) {
                        fputcsv($handle, $row, ',', '"', '\\', "\r\n");
                    }
                    rewind($handle);
                    $response->setContent(stream_get_contents($handle));
                    fclose($handle);
                    break;

                case 'xlsx':
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    // En-têtes
                    $sheet->fromArray($exportData[0], null, 'A1');
                    $sheet->getStyle('A1:E1')->getFont()->setBold(true);

                    // Données
                    $sheet->fromArray(array_slice($exportData, 1), null, 'A2');

                    // Auto-size colonnes
                    foreach (range('A', 'E') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $temp_file = tempnam(sys_get_temp_dir(), 'export_');
                    $writer->save($temp_file);

                    $response = new Response(file_get_contents($temp_file));
                    unlink($temp_file);

                    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    $response->headers->set('Content-Disposition', 'attachment;filename="depenses.xlsx"');
                    break;

                case 'pdf':
                    // Calculs statistiques pour le PDF
                    $dataRows = array_slice($exportData, 1);
                    $totalAmount = array_sum(array_column($dataRows, 2));
                    $avgAmount = count($dataRows) > 0 ? $totalAmount / count($dataRows) : 0;
                    $maxAmount = count($dataRows) > 0 ? max(array_column($dataRows, 2)) : 0;
                    $minAmount = count($dataRows) > 0 ? min(array_column($dataRows, 2)) : 0;

                    // Statistiques par catégorie (si disponible)
                    $statsParCategorie = [];
                    foreach ($dataRows as $row) {
                        $categorie = $row[1] ?? 'Non catégorisé'; // Supposant que la description est en index 1
                        if (!isset($statsParCategorie[$categorie])) {
                            $statsParCategorie[$categorie] = ['count' => 0, 'total' => 0];
                        }
                        $statsParCategorie[$categorie]['count']++;
                        $statsParCategorie[$categorie]['total'] += $row[2];
                    }

                    // Statistiques par période
                    $statsParPeriode = [];
                    foreach ($dataRows as $row) {
                        $date = \DateTime::createFromFormat('d/m/Y H:i:s', $row[0]);
                        if ($date) {
                            $periode = $date->format('Y-m');
                            if (!isset($statsParPeriode[$periode])) {
                                $statsParPeriode[$periode] = ['count' => 0, 'total' => 0];
                            }
                            $statsParPeriode[$periode]['count']++;
                            $statsParPeriode[$periode]['total'] += $row[2];
                        }
                    }

                    $dateMinObj = null;
                    $dateMaxObj = null;
                    if (!empty($data['dateMin'])) {
                        if ($data['dateMin'] instanceof \DateTimeInterface) {
                            $dateMinObj = $data['dateMin'];
                        } else {
                            $dateMinObj = \DateTime::createFromFormat('d/m/Y H:i:s', (string)$data['dateMin']) ?: \DateTime::createFromFormat('Y-m-d H:i:s', (string)$data['dateMin']) ?: new \DateTime((string)$data['dateMin']);
                        }
                    }
                    if (!empty($data['dateMax'])) {
                        if ($data['dateMax'] instanceof \DateTimeInterface) {
                            $dateMaxObj = $data['dateMax'];
                        } else {
                            $dateMaxObj = \DateTime::createFromFormat('d/m/Y H:i:s', (string)$data['dateMax']) ?: \DateTime::createFromFormat('Y-m-d H:i:s', (string)$data['dateMax']) ?: new \DateTime((string)$data['dateMax']);
                        }
                    }

                    $html = $this->renderView('depense/export_pdf.html.twig', [
                        'headers' => $exportData[0],
                        'data' => $dataRows,
                        'stats' => [
                            'total' => $totalAmount,
                            'average' => $avgAmount,
                            'max' => $maxAmount,
                            'min' => $minAmount,
                            'count' => count($dataRows),
                            'parCategorie' => $statsParCategorie,
                            'parPeriode' => $statsParPeriode
                        ],
                        'dateRange' => [
                            'min' => $dateMinObj,
                            'max' => $dateMaxObj,
                            'exportAll' => $exportAll
                        ]
                    ]);

                    $options = new options();
                    $options->set('defaultFont', 'Arial');
                    $options->setIsRemoteEnabled(true);

                    $dompdf = new Dompdf($options);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'landscape');
                    $dompdf->render();

                    $response = new Response($dompdf->output());
                    $response->headers->set('Content-Type', 'application/pdf');
                    $response->headers->set('Content-Disposition', 'attachment;filename="depenses.pdf"');
                    break;

                default:
                    throw new \InvalidArgumentException('Format d\'exportation non valide');
            }

            return $response;
        }

        return $this->render('depense/export.html.twig', [
            'exportForm' => $exportForm->createView(),
        ]);
    }
    #[Route(name: 'app_depense_index', methods: ['GET'])]
    public function index(Request $request, DepenseRepository $depenseRepository, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(DepenseSearchType::class);
        $form->handleRequest($request);

        $query = $depenseRepository->createQueryBuilderForSearch(
            $form->get('montantMin')->getData(),
            $form->get('montantMax')->getData(),
            $form->get('description')->getData(),
            $form->get('dateMin')->getData(),
            $form->get('dateMax')->getData()
        );

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        return $this->render('depense/index.html.twig', [
            'depenses' => $pagination,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_depense_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, CaisseService $caisseService): Response
{
    $depense = new Depense();
    // Définir la date par défaut à maintenant
    $depense->setDateAction(new \DateTimeImmutable());
    $form = $this->createForm(DepenseType::class, $depense);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $depense->setCreatedAt(new \DateTimeImmutable());
        $depense->setUser($this->getUser());

        $entityManager->persist($depense);
        $entityManager->flush();

        // Mettre à jour le solde de la caisse
        $caisseService->updateSolde(-$depense->getMontant());

        $this->addFlash('success', 'La dépense a été ajoutée avec succès.');
        return $this->redirectToRoute('app_depense_index', [], Response::HTTP_SEE_OTHER);
    } elseif ($form->isSubmitted() && !$form->isValid()) {
        $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
    }

    return $this->render('depense/new.html.twig', [
        'depense' => $depense,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_depense_show', methods: ['GET'])]
    public function show(Depense $depense): Response
    {
        return $this->render('depense/show.html.twig', [
            'depense' => $depense,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_depense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Depense $depense, EntityManagerInterface $entityManager, CaisseService $caisseService): Response
    {
        $originalMontant = $depense->getMontant();
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMontant = $depense->getMontant();
            $montantDifference = $originalMontant - $newMontant;

            $entityManager->flush();

            // Mettre à jour le solde avec la différence
            $caisseService->updateSolde($montantDifference);

            $this->addFlash('success', 'La dépense a été modifiée avec succès.');
            return $this->redirectToRoute('app_depense_index', [], Response::HTTP_SEE_OTHER);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('depense/edit.html.twig', [
            'depense' => $depense,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_depense_delete', methods: ['POST'])]
    public function delete(Request $request, Depense $depense, EntityManagerInterface $entityManager, CaisseService $caisseService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$depense->getId(), $request->getPayload()->getString('_token'))) {
            $montant = $depense->getMontant();
            $entityManager->remove($depense);
            $entityManager->flush();

            // Remettre le montant dans la caisse
            $caisseService->updateSolde($montant);
        }

        return $this->redirectToRoute('app_depense_index', [], Response::HTTP_SEE_OTHER);
    }


}
