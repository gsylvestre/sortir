<?php

namespace App\Geolocation;

use GuzzleHttp\Client;

class MapBoxHelper
{
    //devrait être secret !
    const MAPBOX_ACCESS_TOKEN = "pk.eyJ1IjoiZ3N5bHZlc3RyZSIsImEiOiJjazN3MHYzemUwcjRpM2xwaXVidGNwOTluIn0.oNngcvTobTdNcBgg3tcPtg";

    /**
     * En fonction d'une adresse, permet de récupérer les coordonnées (lat et lng)
     */
    public function getAddressCoordinates(string $streetAddress, string $zip, string $city, ?string $country = "fr"): array
    {
        //client http de la librairie externe Guzzle (maintenant, Symfony propose un Client HTTP par défaut)
        $client = new Client();

        //préparation de l'URL à laquelle on fait une requête
        $search = urlencode("$streetAddress $zip $city");
        $token = self::MAPBOX_ACCESS_TOKEN;
        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/$search.json?access_token=$token&country=$country";

        //on fait une reequête à mapbox.com
        $response = $client->get($url);

        //on récupère le contenu de la réponse
        $json = $response->getBody();

        //on transforme ce json en tableau
        $data = json_decode($json, true);

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