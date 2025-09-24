<?php

namespace App\Http\Controllers;
use App\Api\Lidarr;
use App\Api\Spotify;
use App\Helpers\Helpers;
use App\Models\Spotdl;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SpotController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function cron()
    {
        $cronFile = storage_path("cron.sh");
        $fp = fopen($cronFile, "w+");
        fputs($fp, "#!/bin/bash" . PHP_EOL);
        $this->checkLidarr();

        $spotsTodo = [];
        $spotsCheck = [];
        $spotsDone = [];
        $spots = Spotdl::where("avoid","=", false)->get();
        foreach ($spots as $spot){
            if ($spot->isTodo()){
                if (is_dir($spot->getPath())){
                    $files = scandir($spot->getPath());
                    if (($spot->getNbtracks() + 3) <= count($files)){
                        $spot->setDone(1);
                        $spot->save();
                        $spotsDone[] = $spot;
                    }
                }

                if (!$spot->isDone()){
                    $spotsTodo[] = $spot;
                    fputs($fp, 'mkdir -p "' . $spot->getPath(). '"'.PHP_EOL);
                    $docker = env("SPOTIFY_SH")." \"".$spot->getPath()."\" \"". $spot->getSpotifyurl(). "\" 2>&1";
                    fputs($fp, $docker . PHP_EOL);
                }
            } else {
                $spotsCheck[] = $spot;
            }
        }
        fclose($fp);
        chmod($cronFile, 0755);

        return view("spotdl/index", compact('spotsTodo', 'spotsDone', 'spotsCheck'));
    }

    public function save(Request $request){
        if ($request->input("username") != null &&
            $request->input("playlist") != null && $request->input("spotify_url") != null ) {
            $username = $request->input("username");

            $spotdl = Spotdl::where("spotifyurl","=",$request->input("spotify_url") )->first();

            if (!$spotdl) {
                $spotdl = new Spotdl();
            }
            $spotdl->setSpotifyurl($request->input("spotify_url"));
            $spotdl->setArtist($username);
            $spotdl->setTodo(true);
            $spotdl->setAlbum($request->input("playlist"));
            $spotdl->setPath(env("PATH_MUSIC")."/".$username."/") .
                Helpers::replaceCharsFilename($request->input("playlist"));
            if (substr($username,0,1) == "@"){
                $spotdl->setArtist($username);
                $username = substr($username, 1);
                $spotdl->setPath(env("PATH_MUSIC").env("PATH_MUSIC_USER", "/Utilisateurs/")
                    . $username . "/" . Helpers::replaceCharsFilename($request->input("playlist")));
            }

            $spotdl->save();
        }
        return redirect('/admin/download')->withSuccess("Téléchargement ajouté");
    }

    private function checkLidarr(): array
    {
        $spots = [];
        if (env("LIDARR_URL") != "") {
            $lidarr = new Lidarr();
            $spotify = new Spotify();
            $artists = $lidarr->get("/api/v1/artist");

            if ($artists != null) {
                foreach ($artists as $artist) {
                    if ($artist['monitored']) {
                        $artistName = $artist['artistName'];

                        $path = env("PATH_MUSIC") . str_replace("/music","",$artist["path"]);

                        //Recup des albums
                        $albums = $lidarr->get(
                            "/api/v1/album?artistId=" . $artist["id"] . "&includeAllArtistAlbums=true"
                        );
                        foreach ($albums as $album) {
                            if ($album['monitored']) {
                                $albumName = $album['title'];

                                $checkAlbumAlreadyHere = $this->checkAlbumInDir($path, Helpers::replaceCharsFilename($albumName));

                                if (!$checkAlbumAlreadyHere) {
                                    $pathAlbum = $path . "/" . $artistName . " - " . Helpers::replaceCharsFilename($albumName);

                                    //Check sur Spotify
                                    try {
                                        $spotifyUrl = "";
                                        $nbTracks = 0;
                                        $data = $spotify->search($albumName, $artistName);

                                        if (count($data['albums']['items']) > 0) {
                                            $spotifyUrl = $data['albums']['items'][0]["external_urls"]["spotify"];
                                            $nbTracks = $data['albums']['items'][0]["total_tracks"];
                                        }

                                        if ($spotifyUrl != "") {
                                            $spotDl = Spotdl::where("spotifyurl", "=", $spotifyUrl)->first();

                                            if (!$spotDl) {
                                                $spotDl = new Spotdl();
                                                $spotDl->setArtist($artistName);
                                                $spotDl->setAlbum($albumName);
                                                $spotDl->setPath($pathAlbum);
                                                $spotDl->setSpotifyurl($spotifyUrl);
                                                $spotDl->setNbTracks($nbTracks);
                                                $spotDl->save();
                                                $spots[] = $spotDl;
                                            }
                                        }
                                    }catch(\Exception $e){
                                        echo $e->getMessage();
                                        Log::error("Erreurs lors de la recherche de l'album " .$artistName .
                                            " - ".$albumName . " - " . $e->getMessage());
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

    private function checkAlbumInDir($path, $albumName): bool{
        $checkAlbumAlreadyHere = false;
        if (is_dir($path)) {
            $dirs = scandir($path);
            foreach ($dirs as $dir) {
                if ($dir != ".." && $dir != ".") {
                    if (is_dir($path . "/" . $dir)){
                        if (stripos($dir, $albumName) !== false) {
                            $checkAlbumAlreadyHere = true;
                        }

                        if (!$checkAlbumAlreadyHere) {
                            $checkAlbumAlreadyHere = $this->checkAlbumInDir($path . "/" . $dir, $albumName);
                        }
                    }
                }
            }
        }
        return $checkAlbumAlreadyHere;
    }


}
