<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Redirect;
use Closure;
class Checkmaintenance 
{
   
	protected $except = [
        '/maintenance',
    ]; 
	
    public function handle($request, Closure $next)
    {
		if (stripos($_SERVER['REQUEST_URI'],"maintenance") === false){
			if (file_exists(storage_path().'/maintenance.lock')){
				return Redirect::to('/maintenance');
			}
		}
		
		
		//Check if restrict ip
		$tabIp = config("app.ONLY_IP");
		if (count($tabIp)>0){
			if (!in_array($_SERVER['REMOTE_ADDR'],$tabIp) and $_SERVER["REQUEST_URI"] != "/busy"){	
				return Redirect::to('/busy');
			}
		}
		
		return $next($request);
    }
}
