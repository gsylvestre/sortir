<?php

namespace App\Geolocation;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MapBoxHelper
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        //on récupère un client HTTP de Symfony pour exécuter des requêtes HTTP à mapbox
        $this->client = $client;
    }

    //devrait être secret !
    const MAPBOX_ACCESS_TOKEN = "pk.eyJ1IjoiZ3N5bHZlc3RyZSIsImEiOiJjazN3MHYzemUwcjRpM2xwaXVidGNwOTluIn0.oNngcvTobTdNcBgg3tcPtg";

    /**
     * En fonction d'une adresse, permet de récupérer les coordonnées (lat et lng)
     */
    public function getAddressCoordinates(string $streetAddress, string $zip, string $city, ?string $country = "fr"): array
    {
        //préparation de l'URL à laquelle on fait une requête
        $search = urlencode("$streetAddress $zip $city");
        $token = self::MAPBOX_ACCESS_TOKEN;
        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/$search.json?access_token=$token&country=$country";

        //exécute la requête et récupère la réponse
        $response = $this->client->request('GET', $url);

        //on transforme cette réponse (json) en tableau
        $data = $response->toArray();

        //on retourne toujours le premier résultat
        if (!empty($data['features'][0])){
            return [
                'name' => $data['features'][0]['place_name'],
                'lng' => $data['features'][0]['center'][0],
                'lat' => $data['features'][0]['center'][1],
            ];
        }

        return [];
    }
}