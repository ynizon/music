<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Providers\HelperServiceProvider;
use View;

use App\Artist;
use App\Album;
use App\Title;
use App\Repositories\ArtistRepository;
use App\Repositories\AlbumRepository;
use App\Repositories\TitleRepository;

class AjaxController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	
	protected $artistRepository;
	protected $albumRepository;
	protected $titleRepository;
	
	public function __construct(ArtistRepository $artistRepository, AlbumRepository $albumRepository, TitleRepository $titleRepository){
		$this->artistRepository = $artistRepository;
		$this->albumRepository = $albumRepository;
		$this->titleRepository = $titleRepository;
	}
	
	public function artist(Request $request, $artist_name){
		$artist_name = urldecode($artist_name);
		
		//Recup du cache
		$cache = HelperServiceProvider::getCache($artist_name);
		if (isset($cache["biography"]) and isset($cache["albums"]) and isset($cache["similars"]) and isset($cache["videos"]) and isset($cache["lives"])){
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
			$artist = $this->artistRepository->getByName($artist_name);	
			
			if ($artist == null){
				$artist = new Artist();
				$artist->name = strtolower($artist_name);			
			}
			$artist->refreshData();
			
			$biography = View::make('ajax/part-biography', compact('artist'))->render();
			$albums = View::make('ajax/part-albums', compact('artist'))->render();
			$similars = View::make('ajax/part-similars', compact('artist'))->render();
			$videos = View::make('ajax/part-videos', compact('artist'))->render();
			$lives = View::make('ajax/part-lives', compact('artist'))->render();
			
			//Ajout du cache
			HelperServiceProvider::setCache($artist);
		}
		
		$artist_name = strtoupper($artist_name);
		return view('ajax/blocs', compact('biography','albums','similars','videos','lives','artist_name') );
	}
	
	public function artist_album(Request $request, $artist_name, $album_name){
		$artist_name = urldecode($artist_name);
		$album_name = urldecode($album_name);
			
		//Recup du cache
		$cache = HelperServiceProvider::getCache($artist_name, $album_name);
		if (isset($cache["biography"]) and isset($cache["videos"])){
			if (isset($cache["biography"])){
				$biography = $cache["biography"];
			}
			if (isset($cache["videos"])){
				$videos = $cache["videos"];
			}
		}else{
			$album = $this->albumRepository->getByName($artist_name, $album_name);	
			
			if ($album == null){
				$album = new Album();
				$album->artist = strtolower($artist_name);
				$album->name = strtolower($album_name);			
			}
			$album->refreshData();
		
			$info_album = View::make('ajax/part-infoalbum', compact('artist','album'))->render();
			$videos = View::make('ajax/part-videos', compact('artist','album'))->render();
			
			//Ajout du cache
			$artist = $this->artistRepository->getByName($artist_name);	
			HelperServiceProvider::setCache($artist, $album);
		}
		$artist_name = strtoupper($artist_name);
		
		return view('ajax/blocs', compact('biography','videos','lives','artist_name','info_album','album_name') );
	}
	
	public function artist_album_title(Request $request, $artist_name,$album_name, $title_name){
		$artist_name = urldecode($artist_name);
		$album_name = urldecode($album_name);
		$title_name = urldecode($title_name);
		
		//Recup du cache
		$cache = HelperServiceProvider::getCache($artist_name, $album_name, $title_name);
		if (isset($cache["videos"])){
			if (isset($cache["videos"])){
				$videos = $cache["videos"];
			}
		}else{
			$title = $this->titleRepository->getByName($artist_name, $album_name, $title_name);	
			
			if ($title == null){
				$title = new Title();
				$title->artist = strtolower($artist_name);
				$title->album = strtolower($album_name);
				$title->name = strtolower($title_name);			
			}
			$title->refreshData();
			
			//Ajout du cache
			$artist = $this->artistRepository->getByName($artist_name);	
			$album = $this->albumRepository->getByName($artist_name, $album_name);	
			HelperServiceProvider::setCache($artist, $album, $title);
			
			$videos = View::make('ajax/part-videos', compact('artist','album','title'))->render();
		}
		$artist_name = strtoupper($artist_name);
		
		return view('ajax/blocs', compact('videos','artist_name') );
	}
	
	//On requete un truc
	public function keyword(Request $request){
		try{
			$album = new Album();
			$sBio = file_get_contents("https://www.googleapis.com/youtube/v3/search?part=snippet&q=".urlencode($request->input("keywords"))."&maxResults=50&key=".config("app.YOUTUBE_API"));
			if (trim($sBio) != ""){
				$album->youtube =$sBio;
			}
			$lives = null;
			$videos = null;
			if ($request->input("div_id") == "lives_youtube"){
				$lives = View::make('ajax/part-videos', compact('album'))->render();
			}else{
				$videos = View::make('ajax/part-videos', compact('album'))->render();	
			}		
			
			return view('ajax/blocs', compact('videos','lives') );
		}catch(\Exception $e){
			echo $e->getMessage();
		}
	}
	
	public function autocomplete(Request $request){
		$sMode = "";
		$json = array();
		if ("" != ($request->input("term")) and "" != ($request->input("mode"))){
			$sMode = $request->input("mode");
			switch ($sMode){
				case "artist":
					$sArtist = trim($request->input("term"));
					$sArtist = utf8_encode($sArtist);
					$sUrl = "http://ws.audioscrobbler.com/2.0/?method=artist.search&artist=".urlencode(utf8_decode($sArtist))."&lang=fr&format=json&api_key=".config("lastfm.api_key");
					$contenu = json_decode(file_get_contents($sUrl));
					if (is_array($contenu->results->artistmatches->artist)){
						foreach ($contenu->results->artistmatches->artist as $artist){							
							$o = new \StdClass();
							$o->id = $artist->mbid;
							$o->label = HelperServiceProvider::strangeChar($artist->name);
							$json[] = $o;
						}
					}else{
						$o = new \StdClass();
						$o->id = $contenu->results->artistmatches->artist->mbid;
						$o->label = HelperServiceProvider::strangeChar($contenu->results->artistmatches->artist->name);
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
							$o->label = HelperServiceProvider::strangeChar($album->name);
							$json[] = $o;
						}
					}else{			
						$o = new \StdClass();
						$o->id = $contenu->results->albummatches->album->id;
						$o->label = HelperServiceProvider::strangeChar($contenu->results->albummatches->album->name);
						$json[] = $o;
					}
					break;
					
				case "title":
					$sArtist = trim($request->input("q"));
					$sArtist = utf8_encode($sArtist);
					$sTitle = trim($request->input("term"));
					$sTitle = utf8_encode($sTitle);
					$sUrl = "http://ws.audioscrobbler.com/2.0/?method=track.search&artist=".urlencode(utf8_decode($sArtist))."&track=".urlencode(utf8_decode($sTitle))."&lang=fr&format=json&api_key=".config("lastfm.api_key");
					$contenu = json_decode(file_get_contents($sUrl));
					if (is_array($contenu->results->trackmatches->track)){
						foreach ($contenu->results->trackmatches->track as $track){							
							$o = new \StdClass();
							$o->id = $track->name;
							$o->label = HelperServiceProvider::strangeChar($track->name);
							$json[] = $o;
						}
					}else{
						$o = new \StdClass();
						$o->id = $contenu->results->trackmatches->track->id;
						$o->label = HelperServiceProvider::strangeChar($contenu->results->trackmatches->track->name);
						$json[] = $o;
					}
					break;
			}
			
		}
		
		echo json_encode($json);
		exit();
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
