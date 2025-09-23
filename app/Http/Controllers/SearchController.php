<?php

namespace App\Http\Controllers;
use App\Helpers\Helpers;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Spotdl;
use App\Models\Title;
use App\Repositories\AlbumRepository;
use App\Repositories\ArtistRepository;
use App\Repositories\TitleRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class SearchController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct(
        protected ArtistRepository $artistRepository,
        protected AlbumRepository $albumRepository,
        protected TitleRepository $titleRepository){
	}

	public function index(){
		return redirect('/');
	}

    public function go(Request $request, $artist_name){
        $artist_name = urldecode($artist_name);
        $artist_name_recode = $this->artistRepository->fixName($artist_name);
        if ($artist_name_recode != $artist_name){
            header("location: /go/".$artist_name_recode);
        }
        $artist = $this->artistRepository->getBySlug(Str::slug($artist_name));
        if ($artist == null){
            $artist = new Artist(['name'=>$artist_name, 'slug' =>$artist_name]);
        }
        $artist->refreshData();

        return redirect('/artist/'.$artist->slug);
    }

	public function artist(Request $request, $artist_name){
		$artist_name = urldecode($artist_name);
        $artist_name_recode = $this->artistRepository->fixName($artist_name);
        if ($artist_name_recode != $artist_name){
            return redirect("/artist/".$artist_name_recode);
        }

        
		//Recup du cache
		$cache = Helpers::getCache($artist_name);
		if (isset($cache["view"])){echo $cache["view"];exit();}

		$artist = $this->artistRepository->getBySlug(Str::slug($artist_name));

		if ($artist == null){
            $artist = new Artist(['name'=>$artist_name, 'slug' =>$artist_name]);
		}

        $artist->refreshData();

		//Ajout du cache
		Helpers::setCache($artist);

		return view('search/index',  compact('artist'));
	}

	public function artist_album(Request $request, $artist_name, $album_name){
		$artist_name = urldecode($artist_name);
		$artist_name_recode = $this->artistRepository->fixName($artist_name);
        if ($artist_name_recode != $artist_name){
            return redirect("/artist/".$artist_name_recode."/".$album_name);
        }
		$album_name = urldecode($album_name);

		//Recup du cache
		$cache = Helpers::getCache($artist_name, $album_name);
		if (isset($cache["view"])){echo $cache["view"];exit();}

        $artist = $this->artistRepository->getBySlug(Str::slug($artist_name));
        if ($artist == null){
            $artist = new Artist(['name'=>$artist_name, 'slug' =>$artist_name]);
		}
        $artist->refreshData();

		$album = $this->albumRepository->getBySlug(Str::slug($artist_name) . "/". Str::slug($album_name));

		if ($album == null){
            $album = new Album(['name'=>$album_name, 'slug' =>$album_name, 'artist'=>$artist]);
		}
        $album->refreshData();

		if ($album->getName() == "-"){
			$album = null;
		}

		//Ajout du cache
		Helpers::setCache($artist, $album);

		return view('search/index',  compact('artist','album'));
	}

	public function artist_album_title(Request $request, $artist_name, $album_name, $title_name){
		$artist_name = urldecode($artist_name);
		$artist_name = $this->artistRepository->fixName($artist_name);
		$artist_name_recode = $this->artistRepository->fixName($artist_name);
        if ($artist_name_recode != $artist_name){
            return redirect("/artist/".$artist_name_recode."/".$album_name."/".$title_name);
        }
		$album_name = urldecode($album_name);
		$title_name = urldecode($title_name);

		//Recup du cache
		$cache = Helpers::getCache($artist_name, $album_name, $title_name);
		if (isset($cache["view"])){echo $cache["view"];exit();}

        $artist = $this->artistRepository->getBySlug(Str::slug($artist_name));

		if ($artist == null){
            $artist = new Artist(['name'=>$artist_name, 'slug' =>$artist_name]);
		}
		$artist->refreshData();

        $album = $this->albumRepository->getBySlug(Str::slug($artist_name) . "/". Str::slug($album_name));

		if ($album == null){
            $album = new Album(['name'=>$album_name, 'slug' =>$album_name, 'artist'=>$artist]);
		}
        $album->refreshData();

		if ($album->name == "-"){
			$album = null;
		}

        $title = $this->albumRepository->getBySlug(Str::slug($artist_name)
                       . "/". Str::slug($album_name). "/". Str::slug($title_name));

		if ($title == null){
            $title = new Title(['name'=>$title_name, 'slug' =>$title_name, 'artist'=>$artist, 'album'=>$album]);
		}
        $title->refreshData();

		//Ajout du cache
		Helpers::setCache($artist, $album, $title);

		return view('search/index',  compact('artist','album','title'));
	}

	public function picture($mbid, Request $request){
		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);
		header("Content-type: image/png");
		$url = Helpers::getPic($mbid,$request->input('default'));
		echo file_get_contents($url, false, stream_context_create($arrContextOptions));
	}
}
