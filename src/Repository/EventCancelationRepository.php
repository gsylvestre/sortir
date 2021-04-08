<?php

namespace App\Repository;

use App\Entity\EventCancelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventCancelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventCancelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventCancelation[]    findAll()
 * @method EventCancelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventCancelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCancelation::class);
    }

    // /**
    //  * @return EventCancelation[] Returns an array of EventCancelation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EventCancelation
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
