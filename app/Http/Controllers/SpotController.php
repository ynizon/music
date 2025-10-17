<?php

namespace App\Http\Controllers;
use App\Actions\Cron;
use App\Api\Lidarr;
use App\Api\Spotify;
use App\Helpers\Helpers;
use App\Models\Spotdl;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class SpotController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function cron()
    {
// Debug
//        $path = "/share/nas/music/Artistes/Måneskin/";
//        $albumName = "Rush!";
//        $checkAlbumAlreadyHere = false;
//        $this->checkAlbumInDir($checkAlbumAlreadyHere, $path, $albumName);
//
//        if (!$checkAlbumAlreadyHere) {
//
//            echo "xx";
//        }
//        exit();

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
            } else {
                $spotsCheck[] = $spot;
            }
        }

        $cron = new Cron();
        $cron->makeCronFile();
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
            $playlist = Helpers::replaceCharsFilename($request->input("playlist"));
            $spotdl->setSpotifyurl($request->input("spotify_url"));
            $spotdl->setArtist($username);
            $spotdl->setTodo(true);
            $spotdl->setAlbum($request->input("playlist"));
            $spotdl->setPath(env("PATH_MUSIC_ARTIST")."/".$username."/".$username ." - ". $playlist);

            if (substr($username,0,2) == "@-"){
                $username = substr($username, 2);
                $spotdl->setArtist($username);
                $spotdl->setPath(env("PATH_MUSIC_USER") . "/"
                    . $username . "/".Helpers::replaceCharsFilename($request->input("playlist")));
            }

            $spotdl->save();
            $cron = new Cron();
            $cron->makeCronFile();
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

						$path = '';
                        $pathAll = env("PATH_MUSIC") .$artist["path"];
						$infos = explode("/",$pathAll);
						$iPath = 0;
						foreach ($infos as $info){
							if ($iPath == count($infos)-1){
								$info = Helpers::replaceCharsFilename($info);
							}
							$path .= $info."/";
							$iPath++;
						}

                        //Recup des albums
                        $albums = $lidarr->get(
                            "/api/v1/album?artistId=" . $artist["id"] . "&includeAllArtistAlbums=true"
                        );

                        foreach ($albums as $album) {
                            if ($album['monitored']) {
                                $albumName = $album['title'];
								$checkAlbumAlreadyHere = false;
                                $this->checkAlbumInDir($checkAlbumAlreadyHere, $this->replaceArtistPath($path), $albumName);

                                if (!$checkAlbumAlreadyHere) {
                                    $pathAlbum = $path . $artistName . " - " . Helpers::replaceCharsFilename($albumName);

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

    private function checkAlbumInDir(&$checkAlbumAlreadyHere, $path, $albumName){
		if (is_dir($path)) {
            $dirs = scandir($path);

            foreach ($dirs as $dir) {
                if ($dir != ".." && $dir != ".") {
                    if (is_dir($path . $dir)){

						$dir = $this->replaceArtistCharsFilename($dir);
						$albumName2 = $this->replaceArtistCharsFilename($albumName);

                        //Debug
                        //echo $dir." XX ".$albumName2."<br/>";
                        if (stripos($dir, $albumName2) !== false ) {
                            $checkAlbumAlreadyHere = true;
                        }
                    }
                }
            }

			if (!$checkAlbumAlreadyHere) {
				foreach ($dirs as $dir) {
					if ($dir != ".." && $dir != ".") {
						$this->checkAlbumInDir($checkAlbumAlreadyHere, $path . $dir ."/", $albumName);
					}
				}
			}
        }
    }

	private function replaceArtistCharsFilename($s){
        $s = str_replace("–","-",$s);
        $s = str_replace("‐","-",$s);
        $chars = [":","\\","*","°","°","?", "!","¡","+","/",".","=", "-"];
        foreach ($chars as $char){
            $s = str_replace($char," ",$s);
        }
        $s = str_replace("å","a",$s);
		$s = str_replace("|","I",$s);
        $s = str_replace("’","'",$s);
		$s = str_replace("   "," ",$s);
		$s = str_replace("  "," ",$s);
		return trim($s);
	}

    private function replaceArtistPath($s){
        $s = str_replace("å","a",$s);
        return $s;
    }
}
