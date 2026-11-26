<?php

namespace App\Http\Controllers;
use App\Helpers\Helpers;
use App\Repositories\AlbumRepository;
use App\Repositories\ArtistRepository;
use App\Repositories\TitleRepository;
use App\Services\SitemapGenerator;
use Barryvanveen\Lastfm\Lastfm;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function __construct(
        protected ArtistRepository $artistRepository,
        protected AlbumRepository $albumRepository,
        protected TitleRepository $titleRepository)
    {
    }

    public function index(Request $request)
    {
		$dir = storage_path();

		$sMaj = "";
		$interval = 0;
		if (file_exists($dir."/maj.txt")){
			$sMaj = file_get_contents($dir."/maj.txt");
			$datetime1 = date_create(date("Y-m-d"));
			$datetime2 = date_create($sMaj);
			$interval = date_diff($datetime2,$datetime1);
			$interval = $interval->format('%a');
		}

		//Recupere le top artist
        $artistes = [];
		$sFile = $dir."/home.txt";
        if ($sMaj == "" or $interval > 3 or !file_exists($sFile)){
			$sUrl = "http://ws.audioscrobbler.com/2.0/?method=chart.getTopArtists&lang=fr&format=json&api_key=".
                config("lastfm.api_key");

            // Last.fm indisponible : on garde le dernier classement en cache et on réessaiera
            $content = @file_get_contents($sUrl);
            $json = $content ? json_decode($content, true) : null;
            if (isset($json["artists"]['artist']) && is_array($json["artists"]['artist'])) {
                foreach ($json["artists"]['artist'] as $artist) {
                    unset ($artist['image']);
                    $artist['image'] = !empty($artist['mbid']) ? Helpers::getPic($artist['mbid']) : "/images/home_default.png";
                    $artistes[] = $artist;
                }
                file_put_contents($sFile,json_encode($artistes));
                file_put_contents($dir."/maj.txt",date("Y-m-d"));
            } else {
                Log::warning("Last.fm chart.getTopArtists indisponible");
            }
		}
		$artistes = file_exists($sFile) ? (json_decode(file_get_contents($sFile)) ?: []) : [];

		$preferences = array();
		$lastfm_login = Cookie::get('lastfm_login');
		if ($lastfm_login != ""){
			try {
				$lastfm = new Lastfm(new Client(), config("lastfm.api_key"));
				$preferencesTmp = $lastfm->userTopArtists($lastfm_login)->get();
				foreach ($preferencesTmp as $preference){
					$preference['image'] = Helpers::getPic($preference['mbid']);
					$preferences[] = $preference;
				}
			} catch (\Throwable $e) {
				// Last.fm indisponible : la page s'affiche sans les artistes du compte
				Log::warning("Last.fm userTopArtists indisponible : ".$e->getMessage());
			}
		}

		//Et si on en trouve pas alors on prend les artistes smiliaires aux dernieres recherches
		$artist_name = "";
		if ("" != Cookie::get('artist')){
			$artist_name = Cookie::get('artist');
		}

		$similar = array();
		if ($artist_name != ""){
			try{
				$oArtist = $this->artistRepository->getByName($artist_name);
				$similar = json_decode($oArtist->similar);
			}catch(\Exception $e){
				//Nothing
			}
		}

		//On reenregistre le login lastfm
		if ("" != Cookie::get('lastfm_login')){
			$lastfm_login = Cookie::get('lastfm_login');
		}
		Cookie::make("lastfm_login", $lastfm_login, 1314000);

        return view('page/welcome', compact('artistes','preferences','similar','artist_name'));
    }

    public function admin(Request $request){
        $ips = [];
        $error = '';
        if (file_exists(storage_path("ips.txt"))){
            $ips = json_decode(file_get_contents(storage_path("ips.txt")), true);
        }
        if ($request->input("password") != ''){
            if ($request->input("password") == env("ADMIN_PASSWORD")){
                if (!in_array($request->input("ip"), $ips)){
                    $ips[] = $request->input("ip");
                }
                file_put_contents(storage_path("ips.txt"), json_encode($ips));
            } else {
                $error = "Mot de passe faux";
            }
        } else {
            $error = "Mot de passe requis";
        }
        return view('admin/index', compact('ips', 'error'));
    }

	public function lastfm_login(Request $request){
		$lastfm_login = "";
		if ("" != Cookie::get('lastfm_login')){
			$lastfm_login = Cookie::get('lastfm_login');
		}

		if ($request->input("lastfm_login") != "") {
            $lastfm_login = $request->input("lastfm_login");
            // Pseudo last.fm mémorisé pour l'accueil uniquement : la connexion au site passe par /admin/login
            Cookie::queue("lastfm_login", $lastfm_login, 1314000);
			return redirect('/');
		}else{
			return view('lastfm/index', compact('lastfm_login'));
		}
	}

	/**
     * Busy page
     */
    public function busy()
    {
		$dir = dirname(__FILE__)."/../../../storage";

		$sArtistes = "";
		$sMaj = "";
		$interval = 0;
		if (file_exists($dir."/maj.txt")){
			$sMaj = file_get_contents($dir."/maj.txt");
			$datetime1 = date_create(date("Y-m-d"));
			$datetime2 = date_create($sMaj);
			$interval = date_diff($datetime2,$datetime1);
			$interval = $interval->format('%a');
		}

		//Recupere le top artist
		$sFile = $dir."/home.txt";
		if ($sMaj == "" or $interval > 3 or !file_exists($sFile)){
			file_put_contents($dir."/maj.txt",date("Y-m-d"));
			$sUrl = "http://ws.audioscrobbler.com/2.0/?method=chart.getTopArtists&lang=fr&format=json&api_key=".config("lastfm.api_key");
            file_put_contents($sFile,file_get_contents($sUrl));
		}
		$artistes = json_decode(file_get_contents($sFile));

		$preferences = array();
		$lastfm_login = Cookie::get('lastfm_login');
		if ($lastfm_login != ""){
			$lastfm = new Lastfm(new Client(), config("lastfm.api_key"));
			$preferences = $lastfm->userTopArtists($lastfm_login)->get();
		}

		//Et si on en trouve pas alors on prend les artistes smiliaires aux dernieres recherches
		$artist_name = "";
		if ("" != Cookie::get('artist')){
			$artist_name = Cookie::get('artist');
		}

		$similar = array();
		if ($artist_name != ""){
			try{
				$oArtist = $this->artistRepository->getByName($artist_name);
				$similar = json_decode($oArtist->similar);
			}catch(\Exception $e){
				//Nothing
			}
		}

		//On reenregistre le login lastfm
		if ("" != Cookie::get('lastfm_login')){
			$lastfm_login = Cookie::get('lastfm_login');
		}
		Cookie::make("lastfm_login", $lastfm_login, 1314000);

        return view('page/busy', compact('artistes','preferences',
                                         'similar','artist_name'));
    }

	public function faq(Request $request){
		return view('page/faq');
	}

	public function contact(Request $request){
		return view('page/contact');
	}

	public function sitemap(SitemapGenerator $generator){
		set_time_limit(0);
		$generator->refreshStale();

		return response($generator->index(), 200)->header('Content-Type', 'application/xml');
	}

	public function sitemapFile(SitemapGenerator $generator, string $name){
		$file = $generator->file($name);
		abort_if($file === null, 404);

		return response()->file($file, ['Content-Type' => 'application/xml']);
	}


	public function download(Request $request){
		$name = $request->input('name');
		$id = $request->input("id");
		$url = "https://www.youtube.com/watch?v=".$id;
		$dir = storage_path('app').'/download';
		$files = scandir($dir);
		foreach ($files as $file){
			if ($file != "." and $file != ".."){
				unlink($dir.'/'.$file);
			}
		}

		$cmd = 'yt-dlp_linux --prefer-ffmpeg --ffmpeg-location /opt/ffmpeg/ffmpeg --output "'.$dir.'/%(title)s.%(ext)s" --extract-audio --audio-format mp3 '.$url;
		shell_exec($cmd);

		$files = scandir($dir);
		foreach ($files as $file){
			if ($file != "." and $file != ".."){
				return response()->download($dir.'/'.$file);
			}
		}
        return "NE FONCTIONNE PAS SUR CET ORDI: ".$cmd ;
	}
}
