<?php

namespace App\Repositories;

use App\Models\Album;
use Google\Cloud\Datastore\DatastoreClient;

class AlbumRepository {

    //
	protected $model;
	protected $datastore;
	private $table = "Album";

	public function __construct(Album $album)
	{
		$this->model = $album;
		$this->datastore = new DatastoreClient();
	}

	public function getById($id){
		$key = $this->datastore->key($this->table, $this->model->id);
		$album = $this->datastore->lookup($key);
		return $album;
	}

	public function getAll(){
		$query = $this->datastore->query()->kind($this->table);
		$artists = $this->datastore->runQuery($query);
		return $artists;
	}

	public function getByName($artist_name, $name){
		$album = null;
		$query = $this->datastore->query()->kind($this->table)->filter("artist","=",strtolower($artist_name))->filter("name","=",strtolower($name));
		$albums = $this->datastore->runQuery($query);

		foreach ($albums as $entity){
			$fields = $entity->get();
			$album = new Album();

			foreach ($fields as $field=>$value){
				$album->$field = $value;
			}
			$album->id = $entity->key()->pathEndIdentifier();
		}

		return $album;
	}

	public function store(Array $inputs)
	{
		$album = new $this->model;
		$this->save($album, $inputs);

		return $album;
	}

	public function save($album, Array $inputs)
	{
		if (isset($inputs["name"])){
			$this->model->name = $inputs["name"];
		}

		$album->save();
	}

	public function update($id, Array $inputs)
	{
		$this->save($this->getById($id), $inputs);
	}

	public function delete($id){
		$datastore->delete($this->table,$id);
	}

}
