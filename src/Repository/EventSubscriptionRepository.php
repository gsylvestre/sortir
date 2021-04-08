<?php

namespace App\Repository;

use App\Entity\EventSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventSubscription[]    findAll()
 * @method EventSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventSubscription::class);
    }

    // /**
    //  * @return EventSubscription[] Returns an array of EventSubscription objects
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
    public function findOneBySomeField($value): ?EventSubscription
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
