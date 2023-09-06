<?php

namespace App\Repositories;

use App;
use App\Models\User;
use Auth;
use DB;

class UserRepository implements ResourceRepositoryInterface
{

    protected $model;

    public function __construct(User $user)
	{
		$this->model = $user;
	}

	private function save(User $user, Array $inputs)
	{
		if (isset($inputs['name'])){
			$user->name = $inputs['name'];
		}
		
		if ($user->attachments == ""){
			$user->attachments = serialize(array());
		}
		
		if (isset($inputs['nickname'])){
			$user->nickname = $inputs['nickname'];
		}
		
		if (isset($inputs['email'])){
			$user->email = $inputs['email'];
		}
		
		if (isset($inputs['id_manager'])){
			$user->id_manager = $inputs['id_manager'];
		}
				
		if (isset($inputs['status'])){
			$user->status = $inputs['status'];
			
			if ($user->status ==0){
				if (isset($inputs['user_id_reaffect'])){
					if ($inputs['user_id_reaffect'] != "-1"){
						DB::delete("DELETE FROM affectations where user_id = ?",array($user->id));
						DB::update("UPDATE sites SET manager_id = ? WHERE manager_id=?",array($inputs['user_id_reaffect'],$user->id));
						
						//On recupere le mail du nouveau affecte
						$userRepository = App::make('App\Repositories\UserRepository');
						$newuser = $userRepository->getById($inputs['user_id_reaffect']);
						DB::update("UPDATE monitors SET email = ? WHERE email = ?",array($newuser->email,$user->email));
					}
				}
			}
		}
		
		if (isset($inputs['alert_mail'])){
			if ($inputs['alert_mail'] != ""){
				$user->alert_mail = $inputs['alert_mail'];	
			}
		}
		
		if (isset($inputs['password'])){
			if ($inputs['password'] != ""){
				$user->password = bcrypt($inputs['password']);
			}
		}
		$user->save();
		
		//Verif qu il y ai au moins un role
		$lst = DB::select("select count(user_id) as nb from role_user where user_id=?",array($user->id));
		foreach ($lst as $o){
			if ($o->nb == 0){
				$user->roles()->attach(3);//Role user par defaut
			}
		}
		
		//Update des roles
		if (isset($inputs['role'])){
			$lst = DB::select("select id,name from roles",array());
			foreach ($lst as $o){
				if ($user->hasRole($o->name)){
					$user->roles()->detach($o->id);
				}
				if ($inputs['role'] == $o->name){
					$user->roles()->attach($o->id);
				}
			}
		}
	}

	public function getPaginate($n)
	{
		$user = Auth::user();
		return $this->model->paginate($n);
	}

	public function store(Array $inputs)
	{
		$user = new $this->model;
		
		$this->save($user, $inputs);

		return $user;
	}

	public function getById($id)
	{
		$oUser = $this->model->findOrFail($id);
		
		return $oUser;
	}

	public function getByName($name)
	{
		return $this->model->where("name","=",$name)->get();
		
	}
	
	public function update($id, Array $inputs)
	{
		$this->save($this->getById($id), $inputs);
	}

	public function destroy($id)
	{
		$this->getById($id)->delete();
	}

	public function get()
	{
		$usersTmp = $this->model->OrderBy("name")->get();
		$users = array();
		foreach ($usersTmp as $user){
			$users[$user->id] = $user;
		}
		return $users;
		
	}
	
	
	/* Renvoie les admins actifs */
	public function getAdmins()
	{
		$lst = $this->model->where("status","=","1")->OrderBy("name")->get();
		$users = array();
		foreach ($lst as $user ){
			if ($user->hasRole("Admin")){
				$users[] = $user;
			}
		}
		return $users;
		
	}
	
	/* Renvoie les admins / manageurs actifs */
	public function getResponsables()
	{
		$lst = $this->model->OrderBy("name")->where("status","=",1)->get();
		$users = array();
		foreach ($lst as $user ){
			if (!$user->hasRole("Customer")){
				$users[] = $user;
			}
		}
		return $users;
		
	}
	
	public function getActif()
	{
		$users = $this->model->where("status","=","1")->OrderBy("name")->get();
		
		return $users;
		
	}
	
	/* Renvoie les clients */
	public function getCustomers()
	{
		$lst = $this->model->OrderBy("name")->get();
		$users = array();
		foreach ($lst as $user ){
			if ($user->hasRole("Customer")){
				$users[] = $user;
			}
		}
		return $users;
		
	}
	
	/* Renvoie les users pas client */
	public function getUsers()
	{
		$lst = $this->model->OrderBy("name")->get();
		$users = array();
		foreach ($lst as $user ){
			if (!$user->hasRole("Customer")){
				$users[] = $user;
			}
		}
		return $users;
		
	}

	/* Renvoie les users et leurs roles */
	public function getUserAndRoles(){
		$lst = DB::select("select users.name, users.nickname, users.status, users.id , roles.name as role from users 
			INNER JOIN role_user ON role_user.user_id=users.id 
			INNER JOIN roles ON role_user.role_id=roles.id where status = 1 order by name");
		$users = array();
		foreach ($lst as $user ){
			$sRole = $user->role;
			if ($sRole != "Customer"){
				$sRole = "User";
			}
			if (!isset($users[$sRole])){
				$users[$sRole] = array();
			}
			$users[$sRole][$user->id] = $user;
		}
		return $users;
	}
	
	
	/* Renvoie les clients de l' utilisateurs appele depuis la home uniquement */
	public function getMyCustomers(){
		$user = Auth::user();
		if ($user->hasRole("Admin")){
			$o = $this->model->select("users.name","users.id","users.status","users.logged_date") ;
		}
		if ($user->hasRole("Manager")){
			$lst = DB::select ("select distinct id from users where id= ? or id_manager=?",array($user->id,$user->id));
			$tabIds = array();
			foreach ($lst as $l){
				$tabIds[] = $l->id;
			}
			
			$o = $this->model->select("users.name","users.id","users.status","users.logged_date");
			$o = $o->leftJoin('sites', function($join)
			{
				 $join->on('sites.user_id', '=', 'users.id');
			})
			->whereIn("sites.manager_id",$tabIds);
		}
		if ($user->hasRole("User")){
			$o = $this->model->select("users.name","users.id","users.status","users.logged_date");
			$o = $o->leftJoin('sites', function($join)
			{
				 $join->on('sites.user_id', '=', 'users.id');
			})			
			->where("users.id_manager","=",$user->id)
			->Orwhere("sites.manager_id","=",$user->id);
		}		
		if ($user->hasRole("Customer")){
			//Ne devrait pas etre ici
			exit();
		}
		return $o->groupBy("users.id")->groupBy("users.name")->groupBy("users.status")->groupBy("users.logged_date")->orderBy("users.name","asc")->get();
	}
}