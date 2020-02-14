<?php

namespace App\Geolocation;

use GuzzleHttp\Client;

class MapBoxHelper
{
    const MAPBOX_ACCESS_TOKEN = "pk.eyJ1IjoiZ3N5bHZlc3RyZSIsImEiOiJjazN3MHYzemUwcjRpM2xwaXVidGNwOTluIn0.oNngcvTobTdNcBgg3tcPtg";

    public function getAddressCoordinates(string $streetAddress, string $zip, string $city, ?string $country = "fr"): array
    {
        $client = new Client([
            'verify' => false, //désactive la vérif ssl
            'proxy' => [
                'http'  => '10.0.0.248:8080', // Use this proxy with "http"
                'https' => '10.0.0.248:8080', // Use this proxy with "https",
            ]
        ]);

        $search = urlencode("$streetAddress $zip $city");
        $token = self::MAPBOX_ACCESS_TOKEN;
        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/$search.json?access_token=$token&country=$country";

        $response = $client->get($url);
        $json = $response->getBody();
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