<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function search()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e');

        $stateRepo = $this->getEntityManager()->getRepository(EventState::class);

        //que les sorties ouvertes par défaut
        $openState = $stateRepo->findOneBy(['name' => 'open']);
        $qb->andWhere('e.state = :state')
            ->setParameter('state', $openState);

        $qb->leftJoin('e.subscriptions', 'sub')
            ->addSelect('sub');

        //la plus proche dans le temps en premier
        $qb->orderBy('e.startDate', 'ASC');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
}
