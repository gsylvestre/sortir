<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function search(UserInterface $user, ?array $searchData)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e');

        $stateRepo = $this->getEntityManager()->getRepository(EventState::class);

        //que les sorties ouvertes par défaut
        $openState = $stateRepo->findOneBy(['name' => 'open']);
        $qb->andWhere('e.state = :state')
            ->setParameter('state', $openState);

        $qb->leftJoin('e.subscriptions', 'sub')
            ->addSelect('sub')
            ->leftJoin('e.author', 'auth')
            ->addSelect('auth');

        //la plus proche dans le temps en premier
        $qb->orderBy('e.startDate', 'ASC');

        //recherche par mot-clef
        if (!empty($searchData['keyword'])){
            $qb->andWhere('e.name LIKE :kw')
                ->setParameter('kw', '%'.$searchData['keyword'].'%');
        }

        //filtre par site
        if (!empty($searchData['school_site'])){
            $qb->andWhere('auth.school = :school')
                ->setParameter('school', $searchData['school_site']);
        }

        $query = $qb->getQuery();
        $results = $query->getResult();

        //à partir d'ici, je filtre les résultats en PHP
        $tempResults = [];

        //sortie auxquelles je suis inscrit checkbox
        if (!empty($searchData['subscribed_to'])){
            $subscribedTo = array_filter($results, function($event) use ($user){
                /** @var $event Event $sub */
                foreach($event->getSubscriptions() as $sub){
                    if ($sub->getUser()->getId() === $user->getId()){
                        return true;
                    }
                }
                return false;
            });
            $tempResults = array_merge($tempResults, $subscribedTo);
        }

        //sorties pas inscrits
        if (!empty($searchData['not_subscribed_to'])){
            $notSubscribedTo = array_filter($results, function($event) use ($user){
                /** @var $event Event $sub */
                foreach($event->getSubscriptions() as $sub){
                    if ($sub->getUser()->getId() === $user->getId()){
                        return false;
                    }
                }
                return true;
            });
            $tempResults = array_merge($tempResults, $notSubscribedTo);
        }

        $results = $tempResults;

        return $results;
    }
}
