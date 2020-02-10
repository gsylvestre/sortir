<?php

namespace App\Repository;

use App\Entity\TripState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TripState|null find($id, $lockMode = null, $lockVersion = null)
 * @method TripState|null findOneBy(array $criteria, array $orderBy = null)
 * @method TripState[]    findAll()
 * @method TripState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripState::class);
    }

    // /**
    //  * @return TripState[] Returns an array of TripState objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TripState
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
