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

}
