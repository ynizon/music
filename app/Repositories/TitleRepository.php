<?php

namespace App\Repositories;

use App\Models\Title;
use Illuminate\Support\Str;

class TitleRepository {

    public function getByName($artist_name, $album_name, $name){
        return Title::where("artist","=",strtolower($artist_name))
            ->where("album","=",strtolower($album_name))
            ->where("name","=",strtolower($name))->first();
    }

    public function getBySlug($slug){
        return Title::where("slug","=",$slug)->first();
    }
}
