<?php

namespace App;

use Google\Cloud\Datastore\Entity;
use App\Datastoremodel;
use App\Providers\HelperServiceProvider;

class Artist extends DSModel
{
	
    //public $timestamps = true;   
   
	public $table = 'Artist';
	
	public function __construct(){
	
	}
	
	public function refreshData(){
		
		$bRefresh = false;
		$bSave = false;
//		echo var_dump($this);exit();
		
		//Detecter si les infos sont obsoletes		
		if (isset($this->updated_at)){
			$date1 = strtotime($this->updated_at);
			$date2 = strtotime(date("Y-m-d"));		 
			$nbJoursTimestamp = $date2 - $date1;		 
			$nbJours = round($nbJoursTimestamp/86400,0); // 86 400 = 60*60*24

			if ($nbJours>90){
				$bRefresh = true;
			}
		}else{
			$bRefresh = true;
		}
		
		if ($bRefresh or !isset($this->biography)){
			$sBio = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=".urlencode($this->name)."&lang=fr&format=json&api_key=".config("lastfm.api_key"));
			
			$sBioFR = "";
			if (trim($sBio) != ""){
				$this->biography = $sBio;
				$this->lang = "fr";
				$bSave = true;
				$sBioFR = $sBio;
			}			

			//La bio est vide, alors la syntaxe est mauvaise, on prend donc la version UK pour avoir la bonne syntaxe
			if ($sBioFR == ""){
				$sBio = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=".urlencode($this->name)."&format=json&api_key=".config("lastfm.api_key"));
				if (trim($sBio) != ""){
					$this->biography = $sBio;
					$this->lang = "uk";
					$bSave = true;
				}
			}			
		}

		if ($bRefresh or !isset($artist->similar)){
			$sBio = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=artist.getsimilar&artist=".urlencode($this->name)."&lang=fr&format=json&api_key=".config("lastfm.api_key"));
			if (trim($sBio) != ""){
				$this->similar = $sBio;
				$bSave = true;
			}
		}
		if ($bRefresh or !isset($artist->topalbums)){
			$sBio = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=artist.getTopAlbums&artist=".urlencode($this->name)."&lang=fr&format=json&api_key=".config("lastfm.api_key"));			
			if (trim($sBio) != ""){
				$this->topalbums = $sBio;
				$bSave = true;
			}
		}
		if ($bRefresh or !isset($artist->youtube_full_album)){
			$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=".urlencode($this->name." full album")."&maxResults=50&key=".config("app.YOUTUBE_API");
			$sBio = HelperServiceProvider::getYoutubeData($url);
			if (trim($sBio) != ""){
				$this->youtube_full_album = $sBio;
				$bSave = true;
			}
		}
		if ($bRefresh or !isset($artist->youtube_live)){
			$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=".urlencode($this->name." live")."&maxResults=50&key=".config("app.YOUTUBE_API");
			$sBio = HelperServiceProvider::getYoutubeData($url);
			if (trim($sBio) != ""){
				$this->youtube_live = $sBio;
				$bSave = true;
			}
		}
		
		if ($bSave){
			$this->save();
		}
	}
}
