<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use App\Geolocation\MapBoxHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    /**
     * @Route("/api/location/create", name="location_create")
     */
    public function create(Request $request, MapBoxHelper $mapBoxHelper)
    {
        $locationData = $request->request->get('location');

        $cityRepo = $this->getDoctrine()->getRepository(City::class);
        $city = $cityRepo->find($locationData["city"]);

        $location = new Location();
        $location->setCity($city);
        $location->setName($locationData["name"]);
        $location->setStreet($locationData["street"]);
        $location->setZip($locationData["zip"]);

        $coordinates = $mapBoxHelper->getAddressCoordinates($location->getStreet(), $location->getZip(), $city->getName());

        if (!empty($coordinates)){
            $location->setLatitude($coordinates['lat']);
            $location->setLongitude($coordinates['lng']);
        }

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
