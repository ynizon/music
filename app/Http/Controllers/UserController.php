<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Repositories\UserRepository;
use App\Repositories\SiteRepository;
use Session;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use Mail;
use DB;

class UserController extends Controller
{

    protected $userRepository;

    public function __construct(UserRepository $userRepository, SiteRepository $siteRepository)
    {
		$this->userRepository = $userRepository;
		$this->siteRepository = $siteRepository;
	}

	public function index(Request $request)
    {
        //
		if (auth::user()->hasRole("Customer")) {
			return view('errors/403',  array());
			exit();
		}
		
		$usersTmp = $this->userRepository->get();
		$users = $usersTmp;
		if (Auth::user()->hasRole("User")){
			$users = array();
			foreach ($usersTmp as $user){
				if ($user->hasRole("Customer")){
					$users[]=$user;
				}
			}
		}else{
			$users = array();
			foreach ($usersTmp as $user){
				if (!$user->hasRole("Customer")){
					$users[]=$user;
				}
			}
		}
		
		return view('user/index', compact('users'));
	}

	public function create()
	{
		if (auth::user()->hasRole("Customer")) {
			return view('errors/403',  array());
			exit();
		}
		
		$users_roles = config('app.users_roles');
		if (auth::user()->hasRole("User")) {
			unset($users_roles["User"]);
			unset($users_roles["Admin"]);
			unset($users_roles["Manager"]);
		}
		
		$responsables = array(0=>"-");
		$responsablesTmp = $this->userRepository->getResponsables();
		foreach ($responsablesTmp as $r){
			$responsables[$r->id] = $r->name;
		}
		return view('user/create',compact('responsables','users_roles'));
	}
		
	public function show($id)
	{
		return redirect('/user/'.$id."/edit");
	}

	public function edit($id)
	{
		if (auth::user()->hasRole("Customer")) {
			return view('errors/403',  array());
			exit();
		}
		
		$users = $this->userRepository->getUsers();
		$user = $this->userRepository->getById($id);
		$role = "";
		$json= json_decode($user->roles->first());
		if ($json != null){
			$role = $json->name;
		}
		$responsables = array(0=>"-");
		$responsablesTmp = $this->userRepository->getResponsables();
		foreach ($responsablesTmp as $r){
			$responsables[$r->id] = $r->name;
		}
		
		$files = array();

		$directory = config("filesystems.my_storage")."/app/users/".$id;
		if (file_exists($directory)){
			$glob = glob($directory.'/*');

			if ($glob === false) {
				$files = [];
			}

			$files = array_filter($glob, function ($file) {
				return filetype($file) == 'file';
			});
		}
		
		return view('user/edit',  compact('users','user','role','files','responsables'));
	}
	
	
	public function profile(Request $request)
	{	
	
		if (isset($request["password"])){
			//Modification du mot de passe
			$user = Auth::user();
			$this->userRepository->update($user->id, $request->all());

			return redirect('/home')->withOk("Le mot de passe de " . $user->name . " a été mis à jour.");
		}else{
	
			$user = Auth::user();

			return view('user/profile',  compact('user'));
		}
	}

	public function update(UserUpdateRequest $request, $id)
	{
		$this->userRepository->update($id, $request->all());
		$user = $this->userRepository->getById($id);
		$oFileC = new FileController();
		$tabError = $oFileC->replaceFile($request,$user->id,$user,"users",array("jpg","jpeg","png"));
		if (count($tabError)>0){
			return redirect('/users/'.$id.'/edit')->withError("L'utilisateur " . $request->input('name') . " n'a pas été modifié. (".$tabError[0].")" );
		}else{
			if ($user->hasRole("Customer")){
				return redirect('/home')->withOk("L'utilisateur " . $request->input('name') . " a été modifié." );	
			}else{
				return redirect('/users')->withOk("L'utilisateur " . $request->input('name') . " a été modifié." );	
			}
		}
		
	}

	public function destroy($id)
	{
		if (auth::user()->hasRole("Customer")) {
			return view('errors/403',  array());
			exit();
		}
		
		//MAJ des sites (125 = chapon)
		DB::delete("UPDATE sites SET user_id=125 where user_id=?", array($id));
		DB::delete("UPDATE sites SET manager_id=125 where manager_id=?", array($id));
		DB::delete("UPDATE users SET id_manager=0 where id_manager=?", array($id));
		
		//Suppression des donnees annexes
		DB::delete("DELETE FROM affectations where user_id=?", array($id));
		
		//Suppression des fichiers
		$directory = config("filesystems.my_storage")."/app/users/".$id;
		if (file_exists($directory)){
			$glob = glob($directory.'/*');
			$files = array_filter($glob, function ($file) {
				return filetype($file) == 'file';
			});
			foreach ($files as $file){
				unlink($file);
			}
			rmdir($directory);
		}
		
		$this->userRepository->destroy($id);
		return redirect()->back();
	}
	
	public function store(Request $request)
    {
		$user = Auth::user();
	
		try{
			$user = $this->userRepository->store($request->all());
			$oFileC = new FileController();
			$tabError = $oFileC->replaceFile($request,$user->id,$user,"users",array("jpg","jpeg","png"));
			$user->save();
		
			if ($user->hasRole("Customer")) {
				return redirect('/sites/create?id_user='.$user->id)->withOk("L'utilisateur " . $request->input('name') . " a été créé." );
			}else{
				return redirect('/users')->withOk("L'utilisateur " . $request->input('name') . " a été créé." );
			}
		}catch(\Exception $e){
			return redirect('/users')->withError("L'utilisateur " . $request->input('name') . " n'a pas été créé pour la raison suivante: ".$e->getMessage() );
		}

    }
}