<?php

namespace App\Http\Controllers;
use Response;

use Cookie;
use Illuminate\Http\Request;
use App\Repositories\ArtistRepository;
use App\Repositories\TitleRepository;
use App\Repositories\AlbumRepository;
use Barryvanveen\Lastfm\Lastfm;
use GuzzleHttp\Client;
 
class HomeController extends Controller
{
	protected $artistRepository;
	protected $albumRepository;
	protected $titleRepository;
	
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ArtistRepository $artistRepository, AlbumRepository $albumRepository, TitleRepository $titleRepository)
    {
      //  $this->middleware('auth');
	  $this->artistRepository = $artistRepository;
	  $this->albumRepository = $albumRepository;
	  $this->titleRepository = $titleRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
		if ($sMaj == "" or $interval > 3){
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
		Cookie::queue("lastfm_login", $lastfm_login, 1314000);
		
        return view('page/welcome', compact('artistes','preferences','similar','artist_name'));
    }
	
	public function lastfm_login(Request $request){
		$lastfm_login = "";
		if ("" != Cookie::get('lastfm_login')){
			$lastfm_login = Cookie::get('lastfm_login');
		}

		if ($request->input("lastfm_login") != ""){
			$lastfm_login = $request->input("lastfm_login");
			Cookie::queue("lastfm_login", $lastfm_login, 1314000);
			
			if (config("app.ALLOW_OTHER_IPS")){
				$_SESSION["addip"]=$_SERVER['REMOTE_ADDR'];
			}
			
			return redirect('/');
		}else{
			return view('lastfm/index', compact('lastfm_login'));
		}		
	}
	
	/**
     * Busy page
     *
     * @return \Illuminate\Http\Response
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
		if ($sMaj == "" or $interval > 3){
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
		Cookie::queue("lastfm_login", $lastfm_login, 1314000);
		
        return view('page/busy', compact('artistes','preferences','similar','artist_name'));
    }
	
	public function faq(Request $request){
		return view('page/faq');
	}
	
	public function contact(Request $request){
		return view('page/contact');
	}
	
	public function sitemap(Request $request){
		set_time_limit(0);
		
		$dir = dirname(__FILE__)."/../../../storage";
		
		$tabMaj = array();		
		
		$sFile = $dir."/sitemap_maj.txt";
		$tabMaj["artist"] = date("Y-m-d");
		$tabMaj["album"] = date("Y-m-d");
		$tabMaj["title"] = date("Y-m-d");
		
		$interval = 0;
		$datetime1 = date_create(date("Y-m-d"));
		
		$bNew = true;
		if (file_exists($sFile)){
			$bNew = false;
			$tabMaj = json_decode(file_get_contents($sFile),true);			
		}
				
		foreach ($tabMaj as $field=>$sDate){		
			$datetime2 = date_create($sDate);
			$interval = date_diff($datetime2,$datetime1);
			$interval = $interval->format('%a');

			if ($bNew or $interval > 3){			
				switch ($field){
					case "artist":
						$infos = $this->artistRepository->getAll();
						break;
					case "album":
						$infos = $this->albumRepository->getAll();
						break;
					case "title":
						$infos = $this->titleRepository->getAll();						
						break;
				}
				$sitemap_url = config("app.sitemap_url")."/sitemap-".$field.".xml";
				
				$urls = array();
				foreach ($infos as $info){
					switch ($field){
						case "artist":
							$url = config("app.sitemap_url")."/artist/".urlencode(str_replace("/","",$info->name));
							break;
						case "album":
							$url = config("app.sitemap_url")."/artist/".urlencode(str_replace("/","",$info->artist))."/".urlencode(str_replace("/","",$info->name));
							break;
						case "title":
							$url = config("app.sitemap_url")."/artist/".urlencode(str_replace("/","",$info->artist))."/".urlencode(str_replace("/","",$info->album))."/".urlencode(str_replace("/","",$info->name));
							break;
					}
					$urls[$url] = str_replace(" ","T",$info->updated_at)."+00:00";
				}
				
				$content = view('page/sitemap-infos',compact("urls","sitemap_url"));
				file_put_contents($dir."/../public/sitemap-".$field.".xml",$content);
			}
		}
		
		//On ecrit la date du jour pour etre sur que c est MAJ
		foreach ($tabMaj as $field=>$sDate){
			$tabMaj[$field] = date("Y-m-d");
		}
		file_put_contents($sFile,json_encode($tabMaj));
		
		$content = view('page/sitemap');
		return Response::make($content, '200')->header('Content-Type', 'text/xml');
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
		
		$cmd = 'youtube-dl --prefer-ffmpeg --ffmpeg-location /opt/ffmpeg/ffmpeg --output "'.$dir.'/%(title)s.%(ext)s" --extract-audio --audio-format mp3 '.$url;
		shell_exec($cmd);

		$files = scandir($dir);
		foreach ($files as $file){
			if ($file != "." and $file != ".."){
				return response()->download($dir.'/'.$file);
			}
		}
	}
}
