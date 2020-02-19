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
     * @Route("", name="list")
     */
    public function list(Request $request)
    {
        //valeurs par défaut du formulaire de recherche
        //sous forme de tableau associatif, car le form n'est pas associée à une entité
        $searchData = ['subscribed_to' => true, 'not_subscribed_to' => true];
        $searchForm = $this->createForm(EventSearchType::class, $searchData);

        $searchForm->handleRequest($request);

        //on récupère les (éventuelles) données soumises a la mano
        $searchData = $searchForm->getData();

        //appelle ma méthode perso de recherche et filtre
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepo->search($this->getUser(), $searchData);

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * Affichage d'une sortie
     *
     * @Route("/details/{id}", name="detail")
     */
    public function detail(Event $event)
    {
         return $this->render('event/detail.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Création d'une sortie
     *
     * @Route("/ajout", name="create")
     */
    public function create(Request $request)
    {
        $event = new Event();

        //avec des heures par défaut dans le form...
        $event->setStartDate((new \DateTimeImmutable())->setTime(17, 0));
        $event->setRegistrationLimitDate($event->getStartDate()->sub(new \DateInterval("PT1H")));

        $eventForm = $this->createForm(EventType::class, $event);
        $eventStateRepo = $this->getDoctrine()->getRepository(EventState::class);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()){
            //on donne l'état "créée" à cette sortie
            $createdState = $eventStateRepo->findOneBy(['name' => 'created']);
            $event->setState($createdState);

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
        //@TODO: vérifier que c'est bien l'auteur (ou un admin) qui est en train de publier
        //@TODO: vérifier que ça peut être publié (pas annulée, pas closed, etc.)

        $stateHelper->changeEventState($event, "open");
        return $this->redirectToRoute('event_list');
    }


    /**
     * @Route("/{id}/annuler", name="cancel")
     */
    public function cancel(Event $event, EventStateHelper $stateHelper, Request $request)
    {
        //@TODO: vérifier que la sortie n'est pas déjà annulée !

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
