<?php

namespace App\Controller;

use App\Entity\Event;
use App\Geolocation\MapBoxHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(MapBoxHelper $mapbox)
    {
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $allEventCount = $eventRepo->count([]);


        return $this->render('default/home.html.twig', [
            "allEventCount" => $allEventCount,

        ]);
    }
}
