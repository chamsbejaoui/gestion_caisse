<?php

namespace App\Service;

use App\Repository\AlimentationRepository;
use App\Repository\DepenseRepository;

class CaisseService
{
    private AlimentationRepository $alimentationRepository;
    private DepenseRepository $depenseRepository;

    public function __construct(
        AlimentationRepository $alimentationRepository,
        DepenseRepository $depenseRepository
    ) {
        $this->alimentationRepository = $alimentationRepository;
        $this->depenseRepository = $depenseRepository;
    }

    public function calculateSolde(): float
    {
        $totalAlimentations = $this->alimentationRepository->calculateTotalAmount();
        $totalDepenses = $this->depenseRepository->calculateTotalAmount();

        return $totalAlimentations - $totalDepenses;
    }
}