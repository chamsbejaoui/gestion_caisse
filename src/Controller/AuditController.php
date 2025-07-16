<?php

namespace App\Controller;

use App\Entity\Alimentation;
use App\Entity\Categorie;
use App\Entity\Depense;
use App\Entity\User;
use DH\Auditor\Provider\Doctrine\Persistence\Reader\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuditController extends AbstractController
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    #[Route('/audit', name: 'app_audit')]
    public function index(): Response
    {
        // Récupérer l'historique des audits pour chaque entité
        $depenseAudits = $this->reader->createQuery(Depense::class)->execute();
        $alimentationAudits = $this->reader->createQuery(Alimentation::class)->execute();
        $userAudits = $this->reader->createQuery(User::class)->execute();
        $categorieAudits = $this->reader->createQuery(Categorie::class)->execute();

        // Calculer les statistiques
        $depenseStats = [
            'total' => count($depenseAudits),
            'creations' => count(array_filter($depenseAudits, fn($audit) => $audit->getType() === 'insert')),
            'modifications' => count(array_filter($depenseAudits, fn($audit) => $audit->getType() === 'update')),
            'suppressions' => count(array_filter($depenseAudits, fn($audit) => $audit->getType() === 'remove'))
        ];

        $alimentationStats = [
            'total' => count($alimentationAudits),
            'creations' => count(array_filter($alimentationAudits, fn($audit) => $audit->getType() === 'insert')),
            'modifications' => count(array_filter($alimentationAudits, fn($audit) => $audit->getType() === 'update')),
            'suppressions' => count(array_filter($alimentationAudits, fn($audit) => $audit->getType() === 'remove'))
        ];

        $userStats = [
            'total' => count($userAudits),
            'creations' => count(array_filter($userAudits, fn($audit) => $audit->getType() === 'insert')),
            'modifications' => count(array_filter($userAudits, fn($audit) => $audit->getType() === 'update')),
            'suppressions' => count(array_filter($userAudits, fn($audit) => $audit->getType() === 'remove'))
        ];

        $categorieStats = [
            'total' => count($categorieAudits),
            'creations' => count(array_filter($categorieAudits, fn($audit) => $audit->getType() === 'insert')),
            'modifications' => count(array_filter($categorieAudits, fn($audit) => $audit->getType() === 'update')),
            'suppressions' => count(array_filter($categorieAudits, fn($audit) => $audit->getType() === 'remove'))
        ];

        return $this->render('audit/index.html.twig', [
            'depense_audits' => $depenseAudits,
            'alimentation_audits' => $alimentationAudits,
            'user_audits' => $userAudits,
            'categorie_audits' => $categorieAudits,
            'depense_stats' => $depenseStats,
            'alimentation_stats' => $alimentationStats,
            'user_stats' => $userStats,
            'categorie_stats' => $categorieStats,
        ]);
    }
}