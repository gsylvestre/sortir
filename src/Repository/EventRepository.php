<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventState;
use App\Entity\EventSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Event::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère une sortie avec plein de jointures, pour éviter les 10000 requêtes à la bdd
     * Ce sont surtout les récupération des utilisateurs inscrits qui posaient problème
     */
    public function findWithJoins(int $id)
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->andWhere('e.id = :id')->setParameter(':id', $id)

            ->leftJoin('e.state', 's')->addSelect('s')
            ->leftJoin('e.author', 'a')->addSelect('a')
            ->leftJoin('e.subscriptions', 'sub')->addSelect('sub')
            ->leftJoin('sub.user', 'subuser')->addSelect('subuser')
            ->leftJoin('e.location', 'loc')->addSelect('loc');

        return $qb->getQuery()->getOneOrNullResult();
    }



    /**
     * Requête perso à la bdd pour filtrer et rechercher les sorties
     * Reçoit les données du form sous forme de tableau associatif
     *
     * @param UserInterface $user
     * @param array|null $searchData
     * @return array|mixed
     */
    public function search(int $page = 1, int $numPerPage = 10, UserInterface $user, ?array $searchData)
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

        //filtre par date de début minimum
        if (!empty($searchData['start_at_min_date'])){
            $qb->andWhere('e.startDate >= :start_at_min_date')
                ->setParameter('start_at_min_date', $searchData['start_at_min_date']);
        }
        //et date de début maximum
        if (!empty($searchData['start_at_max_date'])){
            $qb->andWhere('e.startDate <= :start_at_max_date')
                ->setParameter('start_at_max_date', $searchData['start_at_max_date']);
        }

        //ce machin crée un ensemble de condition OR entre parenthèses
        //on y ajoute dynamiquement des WHERE plus loin
        $checkBoxesOr = $qb->expr()->orX();

        //récupère l'ids des sorties auxquelles je suis inscrit dans une autre requête
        //ça nous donne un array contenant les ids, qui sera utile pour les IN ou NOT IN plus loin
        $subQueryBuilder = $this->createQueryBuilder('e');
        $subQueryBuilder
            ->from(EventSubscription::class, 'sub')->select("DISTINCT(ev.id)")
            ->join('sub.event', 'ev')->setParameter("user", $user)
            ->andWhere('sub.user = :user');
        $result = $subQueryBuilder->getQuery()->getScalarResult();
        $subcribedToEventIds = array_column($result, "1");

        //inclure les sorties auxquelles je suis inscrit
        if (!empty($searchData['subscribed_to'])){
            $checkBoxesOr->add($qb->expr()->in('sub.event', $subcribedToEventIds));
        }
        //inclure les sorties auxquelles je ne suis pas inscrit
        if (!empty($searchData['not_subscribed_to'])){
            $checkBoxesOr->add($qb->expr()->notIn('sub.event', $subcribedToEventIds));
        }
        //inclure les sorties dont je suis l'organisateur
        if (!empty($searchData['is_organizer'])){
            $checkBoxesOr->add($qb->expr()->eq('auth', $user->getId()));
        }

        //maintenant que nos clauses OR regroupées sont créées, on les ajoute à la requête dans un grand AND()
        $qb->andWhere($checkBoxesOr);


        $count = count($qb->getQuery()->getResult());

        //on récupère les résultats, en fonction des filtres précédent
        $query = $qb->getQuery();

        $pagination = $this->paginator->paginate($query, $page, $numPerPage);

        return $pagination;
    }
}
