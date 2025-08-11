<?php

namespace App\Controller;

use App\Entity\Alimentation;
use App\Form\AlimentationType;
use App\Form\AlimentationSearchType;
use App\Form\ExportOptionsFormType;
use App\Repository\AlimentationRepository;
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
#[Route('/alimentation')]
final class AlimentationController extends AbstractController
{

    #[Route('/export', name: 'app_alimentation_export', methods: ['GET', 'POST'])]
    public function export(Request $request, AlimentationRepository $alimentationRepository): Response
    {
        $exportForm = $this->createForm(ExportOptionsFormType::class);
        $exportForm->handleRequest($request);

        if ($exportForm->isSubmitted() && $exportForm->isValid()) {
            $data = $exportForm->getData();
            $exportAll = $data['exportAll'];
            $format = $data['format'];

            $qb = $alimentationRepository->createQueryBuilder('a');

            if (!$exportAll) {
                $dateMin = $data['dateMin'];
                $dateMax = $data['dateMax'];

                if ($dateMin) {
                    $qb->andWhere('a.createdAt >= :dateMin')
                       ->setParameter('dateMin', $dateMin);
                }
                if ($dateMax) {
                    $qb->andWhere('a.createdAt <= :dateMax')
                       ->setParameter('dateMax', $dateMax);
                }
            }

            $alimentations = $qb->getQuery()->getResult();
            $exportData = [];
            $exportData[] = ['Date', 'Description', 'Montant', 'Créé par'];

            foreach ($alimentations as $alimentation) {
                $exportData[] = [
                    $alimentation->getCreatedAt()->format('d/m/Y H:i:s'),
                    $alimentation->getSource() ?? '',
                    $alimentation->getMontant(),
                    $alimentation->getUser() ? $alimentation->getUser()->getEmail() : ''
                ];
            }

            switch ($format) {
                case 'csv':
                    $response = new Response();
                    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
                    $response->headers->set('Content-Disposition', 'attachment; filename="alimentations.csv"');

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
                    $sheet->getStyle('A1:D1')->getFont()->setBold(true);

                    // Données
                    $sheet->fromArray(array_slice($exportData, 1), null, 'A2');

                    // Auto-size colonnes
                    foreach (range('A', 'D') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $temp_file = tempnam(sys_get_temp_dir(), 'export_');
                    $writer->save($temp_file);

                    $response = new Response(file_get_contents($temp_file));
                    unlink($temp_file);

                    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    $response->headers->set('Content-Disposition', 'attachment;filename="alimentations.xlsx"');
                    break;

                case 'pdf':
                    $html = $this->renderView('alimentation/export_pdf.html.twig', [
                        'headers' => $exportData[0],
                        'data' => array_slice($exportData, 1)
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
                    $response->headers->set('Content-Disposition', 'attachment;filename="alimentations.pdf"');
                    break;

                default:
                    throw new \InvalidArgumentException('Format d\'exportation non valide');
            }

            return $response;
        }

        return $this->render('alimentation/export.html.twig', [
            'exportForm' => $exportForm->createView(),
        ]);
    }
    #[Route(name: 'app_alimentation_index', methods: ['GET'])]
    public function index(Request $request, AlimentationRepository $alimentationRepository, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(AlimentationSearchType::class);
        $form->handleRequest($request);

        $query = $alimentationRepository->createQueryBuilderForSearch(
            $form->get('montantMin')->getData(),
            $form->get('montantMax')->getData(),
            $form->get('dateMin')->getData(),
            $form->get('dateMax')->getData()
        );

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        return $this->render('alimentation/index.html.twig', [
            'alimentations' => $pagination,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_alimentation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, CaisseService $caisseService): Response
{
    $alimentation = new Alimentation();
    // Définir la date par défaut à maintenant
    $alimentation->setDateAction(new \DateTimeImmutable());
    $form = $this->createForm(AlimentationType::class, $alimentation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $alimentation->setCreatedAt(new \DateTimeImmutable());
        $alimentation->setUser($this->getUser());

        $entityManager->persist($alimentation);
        $entityManager->flush();

        // Mettre à jour le solde de la caisse
        $caisseService->updateSolde($alimentation->getMontant());

        $this->addFlash('success', 'L\'alimentation a été ajoutée avec succès.');
        return $this->redirectToRoute('app_alimentation_index', [], Response::HTTP_SEE_OTHER);
    } elseif ($form->isSubmitted() && !$form->isValid()) {
        $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
    }

    return $this->render('alimentation/new.html.twig', [
        'alimentation' => $alimentation,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_alimentation_show', methods: ['GET'])]
    public function show(Alimentation $alimentation): Response
    {
        return $this->render('alimentation/show.html.twig', [
            'alimentation' => $alimentation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_alimentation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Alimentation $alimentation, EntityManagerInterface $entityManager, CaisseService $caisseService): Response
    {
        $originalMontant = $alimentation->getMontant();
        $form = $this->createForm(AlimentationType::class, $alimentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMontant = $alimentation->getMontant();
            $montantDifference = $newMontant - $originalMontant;

            $entityManager->flush();

            // Mettre à jour le solde avec la différence
            $caisseService->updateSolde($montantDifference);

            $this->addFlash('success', 'L\'alimentation a été modifiée avec succès.');
            return $this->redirectToRoute('app_alimentation_index', [], Response::HTTP_SEE_OTHER);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('alimentation/edit.html.twig', [
            'alimentation' => $alimentation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alimentation_delete', methods: ['POST'])]
    public function delete(Request $request, Alimentation $alimentation, EntityManagerInterface $entityManager, CaisseService $caisseService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alimentation->getId(), $request->getPayload()->getString('_token'))) {
            $montant = $alimentation->getMontant();
            $entityManager->remove($alimentation);
            $entityManager->flush();

            // Déduire le montant de la caisse
            $caisseService->updateSolde(-$montant);
        }

        return $this->redirectToRoute('app_alimentation_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
