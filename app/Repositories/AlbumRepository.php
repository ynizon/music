<?php

namespace App\Repositories;

use App\Models\Album;
use Illuminate\Support\Str;

class AlbumRepository {
	public function getByName($artist_name, $name){
		return Album::where("artist","=",strtolower($artist_name))->where("name","=",strtolower($name))->first();
	}

    public function getBySlug($slug){
        return Album::where("slug","=",$slug)->first();
    }
}
