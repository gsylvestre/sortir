<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventCancelation;
use App\Entity\EventState;
use App\EventState\EventStateHelper;
use App\Form\EventCancelationType;
use App\Form\EventSearchType;
use App\Form\EventType;
use App\Form\LocationType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Tout ce qui a trait aux sorties est géré ici
 *
 * @Route("/sorties", name="event_")
 */
class EventController extends AbstractController
{
    /**
     * Liste des sorties et recherche/filtres
     *
     * @Route("/{page}", name="list", requirements={"page": "\d+"})
     */
    public function list(Request $request, int $page = 1)
    {
        //valeurs par défaut du formulaire de recherche
        //sous forme de tableau associatif, car le form n'est pas associée à une entité
        $searchData = [
            'subscribed_to' => true,
            'not_subscribed_to' => true,
            'is_organizer' => true,
            'start_at_min_date' => new \DateTime("- 1 month"),
            'start_at_max_date' => new \DateTime("+ 1 year"),
        ];
        $searchForm = $this->createForm(EventSearchType::class, $searchData);

        $searchForm->handleRequest($request);

        //on récupère les (éventuelles) données soumises a la mano
        $searchData = $searchForm->getData();

        //appelle ma méthode perso de recherche et filtre
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $paginatedEvents = $eventRepo->search($page, 20, $this->getUser(), $searchData);

        return $this->render('event/list.html.twig', [
            'paginatedEvents' => $paginatedEvents,
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * Affichage d'une sortie
     *
     * @Route("/details/{id}", name="detail")
     */
    public function detail($id, EventRepository $eventRepository)
    {

        $event = $eventRepository->findWithJoins($id);

        //seuls les admins et l'auteur peuvent passer ici
        if(!$this->isGranted("ROLE_ADMIN")) {
            if ($event->getState()->getName() === "created" && $event->getAuthor() !== $this->getUser()) {
                throw $this->createNotFoundException("Cette sortie n'existe pas encore !");
            }
        }


        if (!$event){
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        return $this->render('event/detail.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Création d'une sortie
     *
     * @Route("/ajout", name="create")
     */
    public function create(Request $request, EventStateHelper $stateHelper)
    {
        $event = new Event();
        $event->setCreationDate(new \DateTime());

        //avec des heures par défaut dans le form...
        $event->setStartDate((new \DateTimeImmutable())->setTime(17, 0));
        $event->setRegistrationLimitDate($event->getStartDate()->sub(new \DateInterval("PT1H")));

        $eventForm = $this->createForm(EventType::class, $event);
        $eventStateRepo = $this->getDoctrine()->getRepository(EventState::class);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()){
            //on donne l'état "créée" à cette sortie
            $event->setState($stateHelper->getStateByName('created'));

            //on renseigne son auteur (le user actuel)
            $event->setAuthor($this->getUser());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($event);
            $manager->flush();

            $this->addFlash('success', 'Sortie créée, bravo !');
            return $this->redirectToRoute('event_list');
        }

        //formulaire de lieu, pas traité ici ! Il est en effet soumis en ajax, vers une autre route
        $locationForm = $this->createForm(LocationType::class);

        //on passe les 2 forms pour affichage
        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm->createView(),
            'locationForm' => $locationForm->createView()
        ]);
    }

    /**
     * @Route("/{id}/publier", name="publish")
     */
    public function publish(Event $event, EventStateHelper $stateHelper)
    {
        //vérifie que c'est bien l'auteur (ou un admin) qui est en train de publier
        if ($this->getUser() !== $event->getAuthor() && !$this->isGranted("ROLE_ADMIN")){
            throw $this->createAccessDeniedException("Seul l'auteur de cette sortie peut la publier !");
        }

        //vérifie que ça peut être publié (pas annulée, pas closed, etc.)
        if (!$stateHelper->canBePublished($event)){
            $this->addFlash('danger', 'Cette sortie ne peut pas être publiée !');
            return $this->redirectToRoute('event_list');
        }

        $stateHelper->changeEventState($event, "open");

        $this->addFlash('success', 'La sortie est publiée !');
        return $this->redirectToRoute('event_list');
    }


    /**
     * @Route("/{id}/annuler", name="cancel")
     */
    public function cancel(Event $event, EventStateHelper $stateHelper, Request $request)
    {
        //vérifie que la sortie n'est pas déjà annulée ou autre
        if (!$stateHelper->canBeCanceled($event)){
            $this->addFlash('warning', 'Cette sortie ne peut pas être annulée !');
            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        $eventCancelation = new EventCancelation();
        $eventCancelation->setEvent($event);
        $eventCancelation->setCancelDate(new \DateTime());
        $cancelForm = $this->createForm(EventCancelationType::class, $eventCancelation);

        $cancelForm->handleRequest($request);
        if ($cancelForm->isSubmitted() && $cancelForm->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($eventCancelation);
            $em->flush();

            //@TODO: prévenir les inscrits que la sortie a été annulée !

            $stateHelper->changeEventState($event, "canceled");

            $this->addFlash('success', 'La sortie a bien été annulée.');
            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/cancel.html.twig', [
            'event' => $event,
            'cancelForm' => $cancelForm->createView()
        ]);
    }
}
