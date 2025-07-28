<?php

namespace App\Controller;

use App\Repository\DepenseRepository;
use App\Repository\AlimentationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatistiquesController extends AbstractController
{
    #[Route('/statistiques', name: 'app_statistiques')]
    public function index(
        Request $request,
        DepenseRepository $depenseRepository,
        AlimentationRepository $alimentationRepository
    ): Response {
        $periode = $request->query->get('periode', 'mois');
        
        // Format de période pour les requêtes
        $substringLength = match ($periode) {
            'jour' => 10,
            'mois' => 7,
            'annee' => 4,
            default => 7
        };

        // Dépenses
        $totalDepenses = $depenseRepository->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->getQuery()->getSingleScalarResult();
        $depensesParPeriode = $depenseRepository->createQueryBuilder('d')
            ->select(
                sprintf("SUBSTRING(d.createdAt, 1, %d) as periode", $substringLength),
                "SUM(d.montant) as total"
            )
            ->groupBy('periode')
            ->orderBy('periode', 'DESC')
            ->getQuery()->getResult();

        // Alimentations
        $totalAlimentations = $alimentationRepository->createQueryBuilder('a')
            ->select('SUM(a.montant)')
            ->getQuery()->getSingleScalarResult();
        $alimentationsParPeriode = $alimentationRepository->createQueryBuilder('a')
            ->select(
                sprintf("SUBSTRING(a.createdAt, 1, %d) as periode", $substringLength),
                "SUM(a.montant) as total"
            )
            ->groupBy('periode')
            ->orderBy('periode', 'DESC')
            ->getQuery()->getResult();

        // Calcul de l'évolution des alimentations (trendAlimentations)
        // On compare la somme de la période courante à la précédente
        $currentTotal = 0;
        $previousTotal = 0;
        if (count($alimentationsParPeriode) > 0) {
            $currentTotal = $alimentationsParPeriode[0]['total'];
            if (isset($alimentationsParPeriode[1])) {
                $previousTotal = $alimentationsParPeriode[1]['total'];
            }
        }
        if ($previousTotal > 0) {
            $diff = $currentTotal - $previousTotal;
            $percent = ($diff / $previousTotal) * 100;
        } else {
            $diff = $currentTotal;
            $percent = 100;
        }
        if ($diff > 0) {
            $direction = 'up';
            $text = sprintf('+%.2f%%', $percent);
        } elseif ($diff < 0) {
            $direction = 'down';
            $text = sprintf('%.2f%%', $percent);
        } else {
            $direction = 'right';
            $text = '0%';
        }
        $trendAlimentations = [
            'direction' => $direction,
            'text' => $text
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'periode' => $periode,
                'totalDepenses' => $totalDepenses,
                'depensesParMois' => $depensesParPeriode,
                'totalAlimentations' => $totalAlimentations,
                'alimentationsParMois' => $alimentationsParPeriode,
                'trendAlimentations' => $trendAlimentations
            ]);
        }
        return $this->render('statistiques/index.html.twig', [
            'periode' => $periode,
            'totalDepenses' => $totalDepenses,
            'depensesParMois' => $depensesParPeriode,
            'totalAlimentations' => $totalAlimentations,
            'alimentationsParMois' => $alimentationsParPeriode,
            'trendAlimentations' => $trendAlimentations
        ]);
    }
}
