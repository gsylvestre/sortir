<?php

namespace App\Repository;

use App\Entity\EventState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EventState|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventState|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventState[]    findAll()
 * @method EventState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventState::class);
    }

    // /**
    //  * @return EventState[] Returns an array of EventState objects
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
    public function findOneBySomeField($value): ?EventState
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
