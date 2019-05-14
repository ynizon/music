<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Providers\HelperServiceProvider;


class MaintenanceController extends BaseController
{
	
	public function __construct(){
	}
	
	public function index(){
		return response()->view('errors/503', [],503);	
	}
	
	public function admin(Request $request){		
		$sError = "";
		$sMsg ="";
		$sFile = storage_path().'/maintenance.lock';
		if ($request->input("maintenance") != ""){
			if (config("app.maintenance_password") == $request->input("maintenance")){
				//Suppression des fichers en cache
				if ($request->input("cache") == "reset"){
					$dirs = array("artist","album","title");
					foreach ($dirs as $dir){
						if (is_dir(storage_path()."/cache/".$dir)){
							$files = scandir(storage_path()."/cache/".$dir);
							foreach ($files as $file){
								if ($file != ".." and $file != "."){
									unlink(storage_path()."/cache/".$dir."/".$file);	
								}							
							}
						}
					}
					$sMsg ="Le cache a été supprimé.";
				}
				if (file_exists($sFile)){
					unlink($sFile);
				}else{
					touch($sFile);
				}
			}else{
				$sError = "Erreur: code erroné";
			}
		}
		if (file_exists($sFile)){
			$bMaintenance = true;
		}else{
			$bMaintenance = false;
		}

		if ($sError != ""){
			return redirect('/maintenance/admin')->withError($sError);
		}else{
			if ($sMsg != ""){
				return redirect('/maintenance/admin')->withOk($sMsg);
			}else{
				return view('maintenance/admin',compact("bMaintenance"));
			}
		}
		
	}
}
