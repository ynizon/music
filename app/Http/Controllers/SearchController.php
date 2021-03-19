<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Providers\HelperServiceProvider;

use App\Title;
use App\Artist;
use App\Album;
use App\Repositories\ArtistRepository;
use App\Repositories\AlbumRepository;
use App\Repositories\TitleRepository;

class SearchController extends BaseController
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

	public function index(){
		return redirect('/');
	}

	public function artist(Request $request, $artist_name){
		$artist_name = urldecode($artist_name);

		//Recup du cache
		$cache = HelperServiceProvider::getCache($artist_name);
		if (isset($cache["view"])){echo $cache["view"];exit();}

		$artist = $this->artistRepository->getByName($artist_name);

		if ($artist == null){
			$artist = new Artist();
			$artist->name = strtolower($artist_name);
		}
		$artist->refreshData();

		//Ajout du cache
		HelperServiceProvider::setCache($artist);

		return view('search/index',  compact('artist'));
	}

	public function artist_album(Request $request, $artist_name, $album_name){
		$artist_name = urldecode($artist_name);
		$album_name = urldecode($album_name);

		//Recup du cache
		$cache = HelperServiceProvider::getCache($artist_name, $album_name);
		if (isset($cache["view"])){echo $cache["view"];exit();}

		$artist = $this->artistRepository->getByName($artist_name);

		if ($artist == null){
			$artist = new Artist();
			$artist->name = strtolower($artist_name);
		}
		$artist->refreshData();

		$album = $this->albumRepository->getByName($artist_name, $album_name);

		if ($album == null){
			$album = new Album();
			$album->artist = strtolower($artist_name);
			$album->name = strtolower($album_name);
		}
		$album->refreshData();

		if ($album->name == "-"){
			$album = null;
		}

		//Ajout du cache
		HelperServiceProvider::setCache($artist, $album);

		return view('search/index',  compact('artist','album'));
	}

	public function artist_album_title(Request $request, $artist_name, $album_name, $title_name){
		$artist_name = urldecode($artist_name);
		$album_name = urldecode($album_name);
		$title_name = urldecode($title_name);

		//Recup du cache
		$cache = HelperServiceProvider::getCache($artist_name, $album_name, $title_name);
		if (isset($cache["view"])){echo $cache["view"];exit();}

		$artist = $this->artistRepository->getByName($artist_name);

		if ($artist == null){
			$artist = new Artist();
			$artist->name = strtolower($artist_name);
		}
		$artist->refreshData();

		$album = $this->albumRepository->getByName($artist_name, $album_name);

		if ($album == null){
			$album = new Album();
			$album->artist = strtolower($artist_name);
			$album->name = strtolower($album_name);
		}
		$album->refreshData();

		if ($album->name == "-"){
			$album = null;
		}

		$title = $this->titleRepository->getByName($artist_name, $album_name, $title_name);

		if ($title == null){
			$title = new Title();
			$title->artist = strtolower($artist_name);
			$title->album = strtolower($album_name);
			$title->name = strtolower($title_name);
		}
		$title->refreshData();

		//Ajout du cache
		HelperServiceProvider::setCache($artist, $album, $title);

		return view('search/index',  compact('artist','album','title'));
	}

	public function picture($mbid, Request $request){
		header("Content-type: image/png");
		$url = HelperServiceProvider::getPic($mbid,$request->input('default'));
		echo file_get_contents($url);
	}

    public function sonos(Request $request){
        $url = config("app.SONOS_URL");
        header("location: ".$url."?id=".$request->input("id") ."&name=".$request->input("name"));
    }
}
