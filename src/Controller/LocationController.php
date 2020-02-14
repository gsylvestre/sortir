<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    /**
     * @Route("/api/location/create", name="location_create")
     */
    public function create(Request $request)
    {
        $locationData = $request->request->get('location');

        $cityRepo = $this->getDoctrine()->getRepository(City::class);
        $city = $cityRepo->find($locationData["city"]);

        $location = new Location();
        $location->setCity($city);
        $location->setName($locationData["name"]);
        $location->setStreet($locationData["street"]);
        $location->setZip($locationData["zip"]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($location);
        $em->flush();

        $data = [
            "status" => "ok",
            "location" => $location
        ];
        return new JsonResponse($data);
    }
}
