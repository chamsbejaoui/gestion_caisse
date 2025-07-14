<?php

namespace App\Controller;

use App\Service\CaisseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

abstract class AbstractBaseController extends AbstractController
{
    protected CaisseService $caisseService;
    protected Environment $twig;

    public function __construct(CaisseService $caisseService, Environment $twig)
    {
        $this->caisseService = $caisseService;
        $this->twig = $twig;
    }

    public function initialize(): void
    {
        $this->twig->addGlobal('solde_caisse', $this->caisseService->calculateSolde());
    }
}