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

    /**
     * Requête perso à la bdd pour filtrer et rechercher les sorties
     * Reçoit les données du form sous forme de tableau associatif
     *
     * @param UserInterface $user
     * @param array|null $searchData
     * @return array|mixed
     */
    public function search(UserInterface $user, ?array $searchData)
    {
        //un seul query builder, alias de event => e
        $qb = $this->createQueryBuilder('e');
        //on sélectionne les event
        $qb->select('e');

        $stateRepo = $this->getEntityManager()->getRepository(EventState::class);

        //que les sorties ouvertes par défaut + sorties créées par moi
        $openState = $stateRepo->findOneBy(['name' => 'open']);
        $createdState = $stateRepo->findOneBy(['name' => 'created']);
        $closedState = $stateRepo->findOneBy(['name' => 'closed']);

        //ajoute des clauses where par défaut, toujours présentes
        $qb->andWhere('
            e.state = :openState OR e.state = :closedState 
            OR (e.state = :createdState AND e.author = :user) 
        ')
            ->setParameter('openState', $openState)
            ->setParameter('closedState', $closedState)
            ->setParameter('user', $user)
            ->setParameter('createdState', $createdState);

        //jointures toujours présentes, pour éviter que doctrine fasse 10000 requêtes
        $qb->leftJoin('e.subscriptions', 'sub')
            ->addSelect('sub')
            ->leftJoin('e.author', 'auth')
            ->addSelect('auth')
            ->leftJoin('e.cancelation', 'canc')
            ->addSelect('canc');

        //la plus proche dans le temps en premier
        $qb->orderBy('e.startDate', 'ASC');

        //recherche par mot-clef, si applicable
        if (!empty($searchData['keyword'])){
            $qb->andWhere('e.name LIKE :kw')
                ->setParameter('kw', '%'.$searchData['keyword'].'%');
        }

        //filtre par site, si applicable
        if (!empty($searchData['school_site'])){
            $qb->andWhere('auth.school = :school')
                ->setParameter('school', $searchData['school_site']);
        }

        //on récupère tout de suite les résultats, en fonction des filtres précédent
        $query = $qb->getQuery();
        $tempResults = $query->getResult();

        //à partir d'ici, je filtre les résultats en PHP, c'est plus simple pour moi
        //presque sûr que ce serait plus clean avec le qb, mais ça me saoule
        $results = [];

        //inclure les sorties auxquelles je suis inscrit (checkbox) ?
        if (!empty($searchData['subscribed_to'])){
            //stocke les sorties auxquelles je suis inscrit dans cette variable
            $subscribedTo = array_filter($tempResults, function($event) use ($user){
                /** @var $event Event $sub */
                foreach($event->getSubscriptions() as $sub){
                    if ($sub->getUser()->getId() === $user->getId()){
                        return true;
                    }
                }
                return false;
            });

            //fusionne ce tableau avec le tableau de résultat temporaire
            $results = array_merge($tempResults, $subscribedTo);
        }

        //inclure les sorties auxquelles je ne suis pas inscrit ?
        if (!empty($searchData['not_subscribed_to'])){
            //stocke les sorties auxquelles je ne suis pas inscrit dans cette variable
            $notSubscribedTo = array_filter($tempResults, function($event) use ($user){
                /** @var $event Event $sub */
                foreach($event->getSubscriptions() as $sub){
                    if ($sub->getUser()->getId() === $user->getId()){
                        return false;
                    }
                }
                return true;
            });

            //fusionne ce tableau avec le tableau de résultat temporaire
            $results = array_merge($tempResults, $notSubscribedTo);
        }

        return $results;
    }
}
