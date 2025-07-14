<?php

namespace App\Repository;

use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Depense>
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    public function calculateTotalAmount(): float
    {
        $result = $this->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->getQuery()
            ->getSingleScalarResult();
        
        return $result ?? 0.0;
    }

    public function findBySearchCriteria(?float $montantMin = null, ?float $montantMax = null, ?string $description = null, ?\DateTimeInterface $dateMin = null, ?\DateTimeInterface $dateMax = null): array
    {
        $qb = $this->createQueryBuilder('d');

        if ($montantMin !== null) {
            $qb->andWhere('d.montant >= :montantMin')
               ->setParameter('montantMin', $montantMin);
        }

        if ($montantMax !== null) {
            $qb->andWhere('d.montant <= :montantMax')
               ->setParameter('montantMax', $montantMax);
        }

        if ($description !== null) {
            $qb->andWhere('d.description LIKE :description')
               ->setParameter('description', '%' . $description . '%');
        }

        if ($dateMin !== null) {
            $qb->andWhere('d.dateAction >= :dateMin')
               ->setParameter('dateMin', $dateMin);
        }

        if ($dateMax !== null) {
            $qb->andWhere('d.dateAction <= :dateMax')
               ->setParameter('dateMax', $dateMax);
        }

        return $qb->orderBy('d.dateAction', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

//    /**
//     * @return Depense[] Returns an array of Depense objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Depense
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
