<?php

namespace App\Http\Controllers;
use App\Helpers\Helpers;
use App\Models\Spotdl;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class SpotController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function cron()
    {
        $newspots = $this->checkLidarr();
        $spotsDone = [];
        $spots = Spotdl::where("todo","=","1")->get();
        foreach ($spots as $spot){
            //Lancement du docker de download
            $spotsDone[] = $spot;
            $docker = env("SPOTIFY_SH")." \"".$spot->getPath()."\" \"". $spot->getSpotifyurl(). "\" 2>&1";
            shell_exec($docker);
            $spot->delete();
        }

        return view("spotdl/index", compact('newspots', 'spotsDone'));
    }

//    public function playlist(Request $request){
//        if ($request->input("playlist") != null && $request->input("spotify_url") != null ) {
//            $spotDl = Spotdl::where("spotifyurl","=",$request->input("spotify_url") )->first();
//
//            if (!$spotDl) {
//                $spotdl = new Spotdl();
//            }
//            $spotdl->setSpotifyurl($request->input("spotify_url"));
//            $spotdl->setArtist("- PLAYLIST -");
//            $spotdl->setAlbum($request->input("playlist"));
//            $spotdl->setPath(env("PATH_MUSIC").env("PATH_PLAYLIST", "/0.Playlist/").Helpers::replaceCharsFilename($request->input
//                             ("playlist")));
//            $spotdl->save();
//        }
//        return redirect('/spotdl');
//    }

    private function checkLidarr(): array
    {
        $spots = [];
        if (env("LIDARR_URL") != "") {
            $artists = $this->lidarr_api("/api/v1/artist");
            if ($artists != null) {
                foreach ($artists as $artist) {
                    if ($artist['monitored']) {
                        $artistName = $artist['artistName'];

                        //Rep artiste
                        $path = env("PATH_MUSIC") . $artist["path"];

                        if (! is_dir($path)) {
                            mkdir($path);
                            chmod($path, 0777);
                        }

                        //Recup des albums
                        $albums = $this->lidarr_api(
                            "/api/v1/album?artistId=" . $artist["id"] . "&includeAllArtistAlbums=true"
                        );
                        foreach ($albums as $album) {
                            if ($album['monitored']) {
                                $albumName = $album['title'];
                                $checkAlbumAlreadyHere = false;

                                $dirs = scandir($path);
                                foreach ($dirs as $dir) {
                                    if ($dir != ".." && $dir != ".") {
                                        if (stripos($dir, Helpers::replaceCharsFilename($albumName)) !== false) {
                                            $checkAlbumAlreadyHere = true;
                                        }
                                    }
                                }

                                if (! $checkAlbumAlreadyHere) {
                                    $pathAlbum = $path . "/" . $artistName . " - " . Helpers::replaceCharsFilename($albumName);
                                    if (! is_dir($pathAlbum)) {
                                        mkdir($pathAlbum);
                                        chmod($pathAlbum, 0777);
                                    }

                                    //Check sur Spotify
                                    $spotifyUrl = "";
                                    $data = $this->spotify_api($albumName, $artistName);

                                    if (count($data['albums']['items']) > 0) {
                                        $spotifyUrl = $data['albums']['items'][0]["external_urls"]["spotify"];
                                    }

                                    if ($spotifyUrl != "") {
                                        $spotDl = Spotdl::where("spotifyurl", "=", $spotifyUrl)->first();

                                        if (! $spotDl) {
                                            $spotDl = new Spotdl();
                                            $spotDl->setArtist($artistName);
                                            $spotDl->setAlbum($albumName);
                                            $spotDl->setPath($pathAlbum);
                                            $spotDl->setSpotifyurl($spotifyUrl);
                                            $spotDl->save();
                                            $spots[] = $spotDl;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $spots;
    }

    private function lidarr_api($endpoint){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env("LIDARR_URL") . $endpoint);
        $headers = [
            "X-Api-Key: " . env("LIDARR_API")
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);

        return json_decode($response, true);
    }

    private function spotify_api($albumName, $artistName){
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

        // --- Étape 2 : Faire une requête API authentifiée ---

        // L'URL de l'API que vous voulez interroger (ici, pour rechercher des albums de Pink)
        $api_endpoint = 'https://api.spotify.com/v1/search?type=album&q=album:'.urlencode($albumName).'%20artist:'.urlencode($artistName);

        // Réinitialise la session cURL pour une nouvelle requête
        curl_setopt($ch, CURLOPT_URL, $api_endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token // Utilisation du jeton d'accès
        ]);
        curl_setopt($ch, CURLOPT_POST, false); // N'est plus une requête POST

        // Exécution de la requête API réelle
        $api_response = curl_exec($ch);
        $api_data = json_decode($api_response, true);

        if (curl_errno($ch)) {
            die('Erreur cURL lors de la requête API : ' . curl_error($ch));
        }
        return $api_data;
    }
}
