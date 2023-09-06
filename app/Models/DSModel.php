<?php

namespace App\Models;

use Google\Cloud\Datastore\Entity;
use App\Models\Datastoremodel;

abstract class DSModel
{

    //public $timestamps = true;

	protected $table = '';

	//Renvoie un clone de l objet courrant
	protected function getClone(){
		$dsm = new Datastoremodel($this->table);
		$o = $dsm->getEntity();

		foreach (get_object_vars ($this) as $attribute=>$value){
			$o->$attribute = $value;
		}

		if (!isset($o->id)){
			$o->created_at = date("Y-m-d H:i:s");
		}
		$o->updated_at = date("Y-m-d H:i:s");
		return $o;
	}

	//Renvoie un clone de l objet courrant
	protected function getArray(){
		$o = array();

		foreach (get_object_vars ($this) as $attribute=>$value){
			$o[$attribute] = $value;
		}

		if (!isset($o["id"])){
			$o["created_at"] = date("Y-m-d H:i:s");
		}
		$o["updated_at"] = date("Y-m-d H:i:s");
		return $o;
	}

	public function save(){
		try{
			//On creer pas de cache pour les robots (ca prend trop de place pour rien)
			if (stripos($_SERVER['HTTP_USER_AGENT'],"bot")===false){
				$dsm = new Datastoremodel($this->table);

				//On creer un objet identique au notre pour le mettre en base
				$myarray = $this->getArray();

				$noindex = array();
				foreach ($myarray as $attribute=>$value){
					if (!in_array($attribute,array("name","id","artist"))){
						$noindex[] = $attribute;
					}
				}
				$noindexes = array("excludeFromIndexes"=>$noindex);

				if (isset($myarray["id"])){
					//Update
					$transaction = $dsm->getDatastore()->transaction();
					$key = $dsm->getDatastore()->key($this->table, $myarray["id"]);
					unset($myarray['id']);
					$entity = $dsm->getDatastore()->entity($key, $myarray, $noindexes);
					$transaction->upsert($entity);
					$transaction->commit();
				}else{
					//Insert
					$key = $dsm->getDatastore()->key($this->table);
					$entity = $dsm->getDatastore()->entity($key, $myarray, $noindexes);
					$dsm->getDatastore()->insert($entity);

					// return the ID of the created datastore entity
					$this->id = $entity->key()->pathEndIdentifier();

					//$this->id = $dsm->getDatastore()->upsert($myarray);
				}
			}
		}catch (\Exception $e){
			//Le quota doit etre depasse, bah on sauve pas...
		}
	}

}
