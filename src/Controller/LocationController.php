<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Location;
use App\Geolocation\MapBoxHelper;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des lieux
 *
 * Class LocationController
 * @package App\Controller
 */
class LocationController extends AbstractController
{
    /**
     * Méthode appelée en AJAX seulement. Crée une nouvelle location.
     * Voir templates/event/create.html.twig pour le code JS !
     *
     * @Route("/api/location/create", name="location_create")
     */
    public function create(Request $request, MapBoxHelper $mapBoxHelper)
    {
        //récupère les données POST
        $locationData = $request->request->get('location');

        //récupère les infos de la ville associée à ce lieu
        $cityRepo = $this->getDoctrine()->getRepository(City::class);
        $city = $cityRepo->find($locationData["city"]);

        //@TODO: gérer si on ne trouve pas la ville

        //instancie notre Location et l'hydrate avec les données reçues
        $location = new Location();
        $location->setCity($city);
        $location->setName($locationData["name"]);
        $location->setStreet($locationData["street"]);
        $location->setZip($locationData["zip"]);

        //récupère les coordonnées du lieu grâce à MapBox
        //on appelle ici un service créé dans src/Geolocation/, afin de limiter la quantité de code dans le Controller
        //et afin d'organiser correctement notre code
        $coordinates = $mapBoxHelper->getAddressCoordinates($location->getStreet(), $location->getZip(), $city->getName());

        //hydrate les coordonnées reçues dans l'entité
        if (!empty($coordinates)){
            $location->setLatitude($coordinates['lat']);
            $location->setLongitude($coordinates['lng']);
        }

        //sauvegarde en bdd
        $em = $this->getDoctrine()->getManager();
        $em->persist($location);
        $em->flush();

        //les données à renvoyer au code JS
        //status est arbitraire... mais je prend pour acquis que je renverrais toujours cette clé
        //avec comme valeur soit "ok", soit "error", pour aider le traitement côté client
        //je renvois aussi la Location. Pour que ça marche, j'ai implémenté \JsonSerializable dans l'entité, sinon c'est vide
        $data = [
            "status" => "ok",
            "location" => $location
        ];

        //renvoie la réponse sous forme de données JSON
        //le bon Content-Type est automatiquement configuré par cet objet JsonResponse
        return new JsonResponse($data);
    }

    /**
     * Méthode appelée en AJAX seulement. Retourne la liste des villes correspondant à un code postal.
     * @Route("/api/location/cities/search", name="location_find_cities_by_zip")
     */
    public function findCitiesByZip(Request $request, CityRepository $cityRepository)
    {
        $zip = $request->query->get('zip');
        $cities = $cityRepository->findBy(['zip' => $zip], ['name' => 'ASC']);

        return $this->render('location/ajax_cities_list.html.twig', ['cities' => $cities]);
    }
}
