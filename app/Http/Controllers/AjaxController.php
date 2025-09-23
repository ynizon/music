<?php

namespace App\Http\Controllers;
use App\Helpers\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Title;
use App\Repositories\ArtistRepository;
use App\Repositories\AlbumRepository;
use App\Repositories\TitleRepository;
use Illuminate\Support\Str;

class AjaxController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct(
        protected ArtistRepository $artistRepository,
        protected AlbumRepository $albumRepository,
        protected TitleRepository $titleRepository){
	}

	public function artist(Request $request, $artist_name){
		$artist_name = urldecode($artist_name);
        $artist_name = $this->artistRepository->fixName($artist_name);

		//Recup du cache
		$cache = Helpers::getCache($artist_name);
		if (isset($cache["biography"]) && isset($cache["albums"]) && isset($cache["similars"])
            && isset($cache["videos"]) && isset($cache["lives"])){
			if (isset($cache["biography"])){
				$biography = $cache["biography"];
			}
			if (isset($cache["albums"])){
				$albums = $cache["albums"];
			}
			if (isset($cache["similars"])){
				$similars = $cache["similars"];
			}
			if (isset($cache["videos"])){
				$videos = $cache["videos"];
			}
			if (isset($cache["lives"])){
				$lives = $cache["lives"];
			}
		}else{
			$artist = $this->artistRepository->getBySlug(Str::slug($artist_name));

			if ($artist == null){
				$artist = new Artist(["name"=>$artist_name, "slug"=>$artist_name]);
            }
            $artist->refreshData();

			$biography = View::make('livewire/biography', compact('artist'))->render();
			$albums = View::make('livewire/albums', compact('artist'))->render();
			$similars = View::make('livewire/similars', compact('artist'))->render();
			$videos = View::make('livewire/videos', compact('artist'))->render();
			$lives = View::make('livewire/lives', compact('artist'))->render();

            //Ajout du cache
			Helpers::setCache($artist);
		}

		$artist_name = strtoupper($artist_name);
		return view('ajax/blocs', compact('biography','albums','similars',
                                          'videos','lives','artist_name') );
	}

	public function artist_album(Request $request, $artist_name, $album_name){
		$artist_name = urldecode($artist_name);
		$artist_name = $this->artistRepository->fixName($artist_name);
		$album_name = urldecode($album_name);

		//Recup du cache
		$biography = null;
		$lives = null;
		$info_album = null;
		$cache = Helpers::getCache($artist_name, $album_name);
        $artist = $this->artistRepository->getBySlug(Str::slug($artist_name));
        if (isset($cache["biography"]) and isset($cache["videos"])){
			if (isset($cache["biography"])){
				$biography = $cache["biography"];
			}
			if (isset($cache["videos"])){
				$videos = $cache["videos"];
			}
		}else{
            if ($artist == null){
                $artist = new Artist(["name"=>$artist_name, "slug"=>$artist_name]);
            }
            $artist->refreshData();

			$album = $this->albumRepository->getBySlug(Str::slug($artist_name) . "/". Str::slug($album_name));

			if ($album == null){
				$album = new Album(["artist"=>$artist, "name"=>$album_name, "slug"=>$album_name]);
			}
            $album->refreshData();

			$info_album = View::make('livewire/infoalbum', compact('album'))->render();
			$videos = View::make('livewire/videos', compact('album'))->render();

			//Ajout du cache
			Helpers::setCache($artist, $album);
		}
		$artist_name = strtoupper($artist_name);

		return view('ajax/blocs', compact('biography','videos','lives',
                                          'artist_name','info_album','album_name') );
	}

	public function artist_album_title(Request $request, $artist_name,$album_name, $title_name){
		$artist_name = urldecode($artist_name);
		$artist_name = $this->artistRepository->fixName($artist_name);
		$album_name = urldecode($album_name);
		$title_name = urldecode($title_name);

		//Recup du cache
		$cache = Helpers::getCache($artist_name, $album_name, $title_name);
		if (isset($cache["videos"])){
			if (isset($cache["videos"])){
				$videos = $cache["videos"];
			}
		}else{
			$artist = $this->artistRepository->getBySlug(Str::slug($artist_name));
            if ($artist == null){
                $artist = new Artist(["name"=>$artist_name, "slug"=>$artist_name]);
            }
            $artist->refreshData();

            $album = $this->albumRepository->getBySlug(Str::slug($artist_name)."/".Str::slug($album_name));
            if ($album == null){
                $album = new Album(["artist"=>$artist, "name"=>$album_name, "slug"=>$album_name]);
            }
            $album->refreshData();

            $title = $this->titleRepository->getBySlug(Str::slug($artist_name) ."/". Str::slug($album_name)."/".
                                                       Str::slug($title_name));
            if ($title == null){
				$title = new Title(["name"=>$title_name, "artist"=>$artist,
                    "album"=>$album, "slug"=>$title_name]);
			}
            $title->refreshData();

			//Ajout du cache
			Helpers::setCache($artist, $album, $title);

			$videos = View::make('livewire/videos', compact('artist','album','title'))->render();
		}
		$artist_name = strtoupper($artist_name);

		return view('ajax/blocs', compact('videos','artist_name') );
	}

	//On requete un truc
	public function keyword(Request $request){
		try{
			$album = new Album();
			$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=".
                urlencode($request->input("keywords"))."&maxResults=50&key=".env("YOUTUBE_API");
			$sBio = Helpers::getYoutubeData($url);
			if (trim($sBio) != ""){
                $sBio = json_decode($sBio, true);
                $titles = [];
                foreach ($sBio["items"] as $title){
                    $titles[] = ["title"=> $title["snippet"]["title"],
                        "kind" => $title["id"]["kind"],
                        "playlistId" => $title["id"]["playlistId"] ?? "",
                        "videoId" => $title["id"]["videoId"] ?? "",
                        "thumbnail" => $title["snippet"]["thumbnails"]["default"]["url"] ?? "/images/default_rotate.png"
                    ];
                }
				$album->setYoutube($titles);
			}
			$lives = null;
			$videos = null;
			if ($request->input("div_id") == "lives_youtube"){
				$lives = View::make('livewire/videos', compact('album'))->render();
			}else{
				$videos = View::make('livewire/videos', compact('album'))->render();
			}

			return view('ajax/blocs', compact('videos','lives') );
		}catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function autocomplete(Request $request) : JsonResponse{
		$json = array();
		if ("" != ($request->input("term")) and "" != ($request->input("mode"))){
			$sMode = $request->input("mode");
			switch ($sMode){
				case "artist":
					$sArtist = trim($request->input("term"));
					$sArtist = utf8_encode($sArtist);
					$sUrl = "http://ws.audioscrobbler.com/2.0/?method=artist.search&artist=".
                        urlencode(utf8_decode($sArtist))."&lang=fr&format=json&api_key=".config("lastfm.api_key");
					$contenu = json_decode(file_get_contents($sUrl));
					if (is_array($contenu->results->artistmatches->artist)){
						foreach ($contenu->results->artistmatches->artist as $artist){
							$o = new \StdClass();
							$o->id = $artist->mbid;
							$o->label = Helpers::strangeChar($artist->name);
							$json[] = $o;
						}
					}else{
						$o = new \StdClass();
						$o->id = $contenu->results->artistmatches->artist->mbid;
						$o->label = Helpers::strangeChar($contenu->results->artistmatches->artist->name);
						$json[] = $o;
					}
					break;

				case "album":
					$sAlbum = trim($request->input("term"));
					$sAlbum = utf8_encode($sAlbum);
					$sUrl = "http://ws.audioscrobbler.com/2.0/?method=album.search&album=".urlencode(utf8_decode($sAlbum))."&lang=fr&format=json&api_key=".config("lastfm.api_key");
					$contenu = json_decode(file_get_contents($sUrl));
					if (is_array($contenu->results->albummatches->album)){
						foreach ($contenu->results->albummatches->album as $album){
							$o = new \StdClass();
							$o->id = $album->mbid;
							$o->label = Helpers::strangeChar($album->name);
							$json[] = $o;
						}
					}else{
						$o = new \StdClass();
						$o->id = $contenu->results->albummatches->album->id;
						$o->label = Helpers::strangeChar($contenu->results->albummatches->album->name);
						$json[] = $o;
					}
					break;

				case "title":
					$sArtist = trim($request->input("q"));
					$sArtist = utf8_encode($sArtist);
					$sTitle = trim($request->input("term"));
					$sTitle = utf8_encode($sTitle);
					$sUrl = "http://ws.audioscrobbler.com/2.0/?method=track.search&artist=".
                        urlencode(utf8_decode($sArtist))."&track=".
                        urlencode(utf8_decode($sTitle))."&lang=fr&format=json&api_key=".config("lastfm.api_key");
					$contenu = json_decode(file_get_contents($sUrl));
					if (is_array($contenu->results->trackmatches->track)){
						foreach ($contenu->results->trackmatches->track as $track){
							$o = new \StdClass();
							$o->id = $track->name;
							$o->label = Helpers::strangeChar($track->name);
							$json[] = $o;
						}
					}else{
						$o = new \StdClass();
						$o->id = $contenu->results->trackmatches->track->id;
						$o->label = Helpers::strangeChar($contenu->results->trackmatches->track->name);
						$json[] = $o;
					}
					break;
			}

		}
        return response()->json($json);
	}


	public function flip(Request $request, $artist_name){
		$photos = array();

		if (config("app.FLICKR_API") != ""){
			$params = array(
				'api_key'	=> config("app.FLICKR_API"),
				'method'	=> 'flickr.photos.search',
				'tags'	=> $artist_name,
				'format'	=> 'php_serial',
			);
			$encoded_params = array();
			foreach ($params as $k => $v){
				$encoded_params[] = urlencode($k).'='.urlencode($v);
			}

			$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

			$rsp = file_get_contents($url);
			$rsp_obj = unserialize($rsp);
			$tabPhotos = $rsp_obj["photos"]["photo"];

			$i = 1;
			while  ($i <= 2 and count($tabPhotos)>0){
				$oPhoto = $tabPhotos[$i];
				$photos[] ="https://farm".$oPhoto["farm"].".staticflickr.com/".$oPhoto["server"]."/".$oPhoto["id"]."_".$oPhoto["secret"].".jpg";
				$i++;
			}
		}

		return view('search/flip', compact('photos'));
	}
}
