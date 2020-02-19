<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventSubscription;
use App\EventState\EventStateHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Gère les inscriptions aux sorties
 *
 * Class SubscriptionController
 * @package App\Controller
 */
class SubscriptionController extends AbstractController
{
    /**
     * Inscrit un participant à une sortie, OU le désinscrit
     *
     * @Route("/sorties/{id}/inscription/", name="subscription_toggle")
     */
    public function toggle(Event $event, EventStateHelper $stateHelper)
    {
        $em = $this->getDoctrine()->getManager();
        $subscriptionRepo = $this->getDoctrine()->getRepository(EventSubscription::class);

        //la sortie doit être dans l'état OPEN pour qu'on puisse s'y inscrire
        if ($event->getState()->getName() !== "open"){
            $this->addFlash("danger", "Cette sortie n'est pas ouverte aux inscriptions !");
            return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
        }

        //désincription si on trouve cette inscription
        //on la recherche dans la bdd du coup...
        $foundSubscription = $subscriptionRepo->findOneBy(['user' => $this->getUser(), 'event' => $event]);
        if ($foundSubscription){
            //supprime l'inscription
            $em->remove($foundSubscription);
            $em->flush();

            $this->addFlash("success", "Vous êtes désinscrit !");
            return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
        }

        //sinon, si on ne l'a pas trouvée dans la bdd, c'est qu'on s'inscrit
        //la sortie est-elle complète ?
        //voir dans Entity/Event.php pour cette méthode isMaxedOut()
        if ($event->isMaxedOut()){
            $this->addFlash("danger", "Cette sortie est complète !");
            return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
        }

        //si on s'est rendu jusqu'ici, c'est que tout est ok. On crée et sauvegarde l'inscription.
        $subscription = new EventSubscription();
        $subscription->setUser($this->getUser());
        $subscription->setEvent($event);

        $em->persist($subscription);
        $em->flush();

        //on refresh la sortie pour avoir le bon nombre d'inscrits avant le tchèque ci-dessous
        $em->refresh($event);

        //maintenant, on tchèque si c'est complet pour changer son état
        if ($event->isMaxedOut()){
            $stateHelper->changeEventState($event, 'closed');
        }

        $this->addFlash("success", "Vous êtes inscrit !");
        return $this->redirectToRoute('event_detail', ["id" => $event->getId()]);
    }
}