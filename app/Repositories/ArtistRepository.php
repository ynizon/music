<?php

namespace App\Repositories;

use App\Models\Artist;
use Google\Cloud\Datastore\DatastoreClient;
use Cookie;

class ArtistRepository {

    //
	protected $model;
	protected $datastore;
	private $table = "Artist";

	public function __construct(Artist $artist)
	{
		$this->model = $artist;
		$this->datastore = new DatastoreClient();
	}

	public function getById($id){
		$key = $this->datastore->key($this->table, $this->model->id);
		$artist = $this->datastore->lookup($key);
		return $artist;
	}

	public function getAll(){
		$query = $this->datastore->query()->kind($this->table);
		$artists = $this->datastore->runQuery($query);
		return $artists;
	}

	public function getByName($name){
		Cookie::queue("artist", $name, 1314000);
		$artist = null;

		$query = $this->datastore->query()->kind($this->table)->filter("name","=",strtolower($name));
		$artists = $this->datastore->runQuery($query);

		foreach ($artists as $entity){
			$fields = $entity->get();
			$artist = new Artist();

			foreach ($fields as $field=>$value){
				$artist->$field = $value;
			}
			$artist->id = $entity->key()->pathEndIdentifier();
		}

		return $artist;
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
