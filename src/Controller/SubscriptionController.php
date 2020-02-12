<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventState;
use App\Entity\EventSubscription;
use App\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class SubscriptionController extends AbstractController
{
    /**
     * @Route("/sorties/{id}/inscription/", name="subscription_toggle")
     */
    public function toggle(Event $event)
    {
        $em = $this->getDoctrine()->getManager();

        $subscriptionRepo = $this->getDoctrine()->getRepository(EventSubscription::class);
        $foundSubscription = $subscriptionRepo->findOneBy(['user' => $this->getUser(), 'event' => $event]);

        //désincription si on trouve cette inscription
        if ($foundSubscription){
            $em->remove($foundSubscription);
            $em->flush();

            $this->addFlash("success", "Vous êtes désinscrit !");
            return $this->redirectToRoute('event_list');
        }

        //sinon, inscription
        //complet ?
        if ($event->getMaxRegistrations() !== null && $event->getSubscriptions()->count() >= $event->getMaxRegistrations()){
            $this->addFlash("danger", "Cette sortie est complète !");
            return $this->redirectToRoute('event_list');
        }

        $subscription = new EventSubscription();
        $subscription->setUser($this->getUser());
        $subscription->setEvent($event);

        $em->persist($subscription);
        $em->flush();

        $this->addFlash("success", "Vous êtes inscrit !");
        return $this->redirectToRoute('event_list');
    }
}