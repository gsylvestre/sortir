<?php

namespace App\Repository;

use App\Entity\SchoolSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SchoolSite|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolSite|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolSite[]    findAll()
 * @method SchoolSite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolSiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchoolSite::class);
    }

    // /**
    //  * @return SchoolSite[] Returns an array of SchoolSite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SchoolSite
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
