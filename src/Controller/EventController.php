<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sorties", name="event_")
 */
class EventController extends AbstractController
{
    /**
     * @Route("", name="list")
     */
    public function list()
    {
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepo->search();

        return $this->render('event/list.html.twig', [
            'events' => $events
        ]);
    }

    /**
     * @Route("/ajout", name="create")
     */
    public function create(Request $request)
    {
        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()){
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($event);
            $manager->flush();

            $this->addFlash('success', 'Sortie créée, bravo !');
            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm->createView()
        ]);
    }
}
