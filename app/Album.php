<?php

namespace App;

use Google\Cloud\Datastore\Entity;
use App\Datastoremodel;
use App\Providers\HelperServiceProvider;

class Album extends DSModel
{
	
    //public $timestamps = true;   
   
	public $table = 'Album';
	
	public function __construct(){
	
	}
	
	public function refreshData(){
		///echo var_dump($artist);exit();
		$bRefresh = false;
		$bSave = false;
//		echo var_dump($this);exit();

		if ($this->name != "-"){
			//Detecter si les infos sont obsoletes
			if (isset($this->updated_at)){
				$date1 = strtotime($this->updated_at);
				$date2 = strtotime(date("Y-m-d"));		 
				$nbJoursTimestamp = $date2 - $date1;		 
				$nbJours = round($nbJoursTimestamp/86400,0); // 86 400 = 60*60*24

				if ($nbJours>360){
					$bRefresh = true;
				}		
			}else{
				$bRefresh = true;
			}
			
			if ($bRefresh or !isset($this->youtube)){
				$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=".urlencode($this->artist." ".$this->name)."&maxResults=50&key=".config("app.YOUTUBE_API");
				$sBio = HelperServiceProvider::getYoutubeData($url);
				if (trim($sBio) != ""){
					$this->youtube =$sBio;
					$bSave = true;
				}
			}

			if ($bRefresh or !isset($this->info)){
				$sBio = file_get_contents("http://ws.audioscrobbler.com/2.0/?method=album.getinfo&artist=".urlencode($this->artist)."&lang=fr&album=".urlencode($this->name)."&format=json&api_key=".config("lastfm.api_key"));
				if (trim($sBio) != ""){
					$this->info = $sBio;
					$bSave = true;
				}
			}			
			
			if ($bSave){
				$this->save();
			}
		}
	}
}
