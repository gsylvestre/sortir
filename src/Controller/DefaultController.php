<?php

namespace App\Controller;

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
        $data = $mapbox->getAddressCoordinates("Centre atlantis", "", "saint-herblain");
        dump($data);

        return $this->render('default/home.html.twig', [
        ]);
    }
}
