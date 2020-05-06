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
		
		return $next($request);
    }
}
