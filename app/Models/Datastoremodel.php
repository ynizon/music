<?php

namespace App\Models;

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;

class Datastoremodel extends Entity
{
	protected $table = '';
	private $datastore = null;
	
	public function __construct($table){
		$this->table = $table ;
		$this->datastore = new DatastoreClient();
		$this->datastore->entity($this->table);
	}
	public function getDatastore(){
		return $this->datastore;
	}
	
	public function getEntity(){
		return $this->getDatastore()->entity($this->table);	
	}
	
}
