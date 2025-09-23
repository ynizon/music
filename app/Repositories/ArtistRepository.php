<?php

namespace App\Repositories;

use App\Models\Artist;
use Illuminate\Support\Str;

class ArtistRepository {

	public function __construct()
	{
	}

    public function getBySlug($slug){
        return Artist::where("slug","=",$slug)->first();
    }

	public function getByName($name){
		return Artist::where("name","=",strtolower($name))->first();
	}

	public function store(Array $inputs)
	{
		$artist = new $this->model;
		$this->save($artist, $inputs);

		return $artist;
	}

	public function save($artist, Array $inputs)
	{
		if (isset($inputs["name"])){
			$this->model->name = $inputs["name"];
		}

		$artist->save();
	}

	public function update($id, Array $inputs)
	{
		$this->save($this->getById($id), $inputs);
	}

	public function delete($id){
		$datastore->delete($this->table,$id);
	}

    public function fixName(string $artistName ): string {
        $url = "http://musicbrainz.org/ws/2/artist/?query=".urlencode($artistName)."&fmt=json";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Music.gameandme.fr ( ynizon@gmail.com )');
        
        if (preg_match('`^https://`i', $url)){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json =curl_exec($ch);
        curl_close($ch);
        
        if ($json != ""){
            $json = json_decode($json, true);
            if (isset($json['artists'][0]['name'])){
                $artistName = $json['artists'][0]['name'];
            }
        }
        
        return $artistName;
    }
}
