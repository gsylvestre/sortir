<?php

namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des pages types "divers"
 *
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * Page d'accueil du site
     *
     * @Route("/", name="home")
     */
    public function home()
    {
        //juste pour récupérer le nombre total de sorties
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        //sans filtre avec []
        $allEventCount = $eventRepo->count([]);

        return $this->render('default/home.html.twig', [
            "allEventCount" => $allEventCount,
        ]);
    }
}
