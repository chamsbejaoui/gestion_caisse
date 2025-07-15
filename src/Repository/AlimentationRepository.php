<?php

namespace App\Repository;

use App\Entity\Alimentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alimentation>
 */
class AlimentationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alimentation::class);
    }

    public function findBySearchCriteria(?float $montantMin = null, ?float $montantMax = null, ?string $description = null, ?\DateTimeInterface $dateMin = null, ?\DateTimeInterface $dateMax = null): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($montantMin !== null) {
            $qb->andWhere('a.montant >= :montantMin')
               ->setParameter('montantMin', $montantMin);
        }

        if ($montantMax !== null) {
            $qb->andWhere('a.montant <= :montantMax')
               ->setParameter('montantMax', $montantMax);
        }

        if ($description !== null) {
            $qb->andWhere('a.description LIKE :description')
               ->setParameter('description', '%' . $description . '%');
        }

        if ($dateMin !== null) {
            $qb->andWhere('a.dateAction >= :dateMin')
               ->setParameter('dateMin', $dateMin);
        }

        if ($dateMax !== null) {
            $qb->andWhere('a.dateAction <= :dateMax')
               ->setParameter('dateMax', $dateMax);
        }

        return $qb->orderBy('a.dateAction', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

//    /**
//     * @return Alimentation[] Returns an array of Alimentation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Alimentation
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
