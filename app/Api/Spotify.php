<?php

namespace App\Api;

use Aerni\Spotify\PendingRequest;
use Aerni\Spotify\Spotify as OldSpotify;

class Spotify extends OldSpotify
{
    public function __construct()
    {
        $defaultConfig = [
            'country' => config('spotify.default_config.country'),
            'locale' => config('spotify.default_config.locale'),
            'market' => config('spotify.default_config.market'),
        ];
        parent::__construct($defaultConfig);
    }

    /**
     * Get Spotify catalog information about an artist’s albums. Optional parameters can be specified in the query string to filter and sort the response.
     *
     * @param string $id
     * @return PendingRequest
     */
    public function artistAlbums(string $id): PendingRequest
    {
        $endpoint = '/artists/'.$id.'/albums/';

        $acceptedParams = [
            'include_groups' => null,
            'country' => $this->defaultConfig['country'],
            'limit' => 50, //INCREASE LIMIT MAX
            'offset' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams);
    }

    public function search($albumName, $artistName){
        $client_id = env("SPOTIFY_CLIENT_ID");
        $client_secret = env("SPOTIFY_CLIENT_SECRET");

        // L'URL de l'API d'authentification de Spotify
        $auth_url = 'https://accounts.spotify.com/api/token';

        $ch = curl_init();

        // Préparation des données POST
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
              'grant_type' => 'client_credentials',
          ]));

        // Ajout de l'en-tête d'autorisation (encodage Base64)
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        // Exécution de la requête pour obtenir le jeton
        $auth_response = curl_exec($ch);
        $auth_data = json_decode($auth_response, true);

        if (curl_errno($ch)) {
            die('Erreur cURL lors de l\'authentification : ' . curl_error($ch));
        }
        if (!isset($auth_data['access_token']))
        {
            die('Erreur  : ' . $auth_data["error"]);
        }
        $access_token = $auth_data['access_token'];

        if (!isset($access_token)) {
            die('Erreur : Jeton d\'accès non reçu.');
        }

        $api_endpoint = 'https://api.spotify.com/v1/search?type=album&q=album%3A'.urlencode($albumName).'%20artist%3A'.urlencode($artistName);

        curl_setopt($ch, CURLOPT_URL, $api_endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token // Utilisation du jeton d'accès
        ]);
        curl_setopt($ch, CURLOPT_POST, false); // N'est plus une requête POST

        $api_response = curl_exec($ch);
        $api_data = json_decode($api_response, true);

        if (curl_errno($ch)) {
            die('Erreur cURL lors de la requête API : ' . curl_error($ch));
        }
        return $api_data;
    }
}
