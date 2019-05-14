<?php 

namespace App\Repositories;

use App\Title;
use Google\Cloud\Datastore\DatastoreClient;

class TitleRepository {

    //
	protected $model;
	protected $datastore;
	private $table = "Title";

	public function __construct(Title $title)
	{
		$this->model = $title;
		$this->datastore = new DatastoreClient();
	}

	public function getById($id){
		$key = $this->datastore->key($this->table, $this->model->id);
		$title = $this->datastore->lookup($key);
		return $title;
	}
	
	public function getAll(){
		$query = $this->datastore->query()->kind($this->table);
		$artists = $this->datastore->runQuery($query);
		return $artists;
	}	

	public function getByName($artist_name, $album_name, $name){		
		$title = null;
		$query = $this->datastore->query()->kind($this->table)->filter("artist","=",strtolower($artist_name))->filter("album","=",strtolower($album_name))->filter("name","=",strtolower($name));
		$titles = $this->datastore->runQuery($query);
		
		foreach ($titles as $entity){
			$fields = $entity->get();
			$title = new Title();
			
			foreach ($fields as $field=>$value){
				$title->$field = $value;
			}
			$title->id = $entity->key()->pathEndIdentifier();
		}

		return $title;
	}
	
	public function store(Array $inputs)
	{
		$title = new $this->model;		
		$this->save($title, $inputs);

		return $title;
	}
	
	public function save($title, Array $inputs)
	{
		if (isset($inputs["name"])){
			$this->model->name = $inputs["name"];
		}		
        
		$title->save();		
	}	
	
	public function update($id, Array $inputs)
	{
		$this->save($this->getById($id), $inputs);
	}	
	
	public function delete($id){
		$datastore->delete($this->table,$id);
	}

}