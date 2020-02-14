<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventState;
use App\Entity\EventSubscription;
use App\EventState\EventStateHelper;
use App\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class SubscriptionController extends AbstractController
{
    /**
     * @Route("/sorties/{id}/inscription/", name="subscription_toggle")
     */
    public function toggle(Event $event, EventStateHelper $stateHelper)
    {
        $em = $this->getDoctrine()->getManager();
        $subscriptionRepo = $this->getDoctrine()->getRepository(EventSubscription::class);

        //un peu de validations
        if ($event->getState()->getName() !== "open"){
            $this->addFlash("danger", "Cette sortie n'est pas ouverte aux inscriptions !");
            return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
        }

        //désincription si on trouve cette inscription
        $foundSubscription = $subscriptionRepo->findOneBy(['user' => $this->getUser(), 'event' => $event]);
        if ($foundSubscription){
            $em->remove($foundSubscription);
            $em->flush();

            $this->addFlash("success", "Vous êtes désinscrit !");
            return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
        }

        //sinon, inscription
        //complet ?
        if ($event->isMaxedOut()){
            $this->addFlash("danger", "Cette sortie est complète !");
            return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
        }

        $subscription = new EventSubscription();
        $subscription->setUser($this->getUser());
        $subscription->setEvent($event);

        $em->persist($subscription);
        $em->flush();

        $em->refresh($event);

        //maintenant, on tchèque si c'est complet pour changer son état
        if ($event->isMaxedOut()){
            $stateHelper->changeEventState($event, 'closed');
        }

        $this->addFlash("success", "Vous êtes inscrit !");
        return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
    }
}