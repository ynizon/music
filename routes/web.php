<?php

use App\Models\Ip;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpotController;

Route::get('/busy', [HomeController::class,'busy']);
Route::get('/home', [HomeController::class,'index'])->name('home');
Route::get('/lastfm_login', [HomeController::class,'lastfm_login']);
Route::post('/lastfm_login', [HomeController::class,'lastfm_login']);

Route::get('/cron', [SpotController::class,'cron']);

if (!App::runningInConsole()) {
	if (env("app.RESTRICT_IP") == false){
        $allowedIps = [$_SERVER['REMOTE_ADDR']];
    } else {
        $allowedIps = [];
        if (Ip::all()->count() > 0){
            foreach (Ip::all() as $ip){
                $allowedIps[] = $ip['ip'];
            }
        }
    }

	if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$allowedIps)){
		Route::get('/', [HomeController::class,'index']);
		Route::get('/sitemap.xml', [HomeController::class,'sitemap']);
		Route::get('/contact', [HomeController::class,'contact']);
		Route::get('/faq', [HomeController::class,'faq']);
		Route::get('/download', [HomeController::class,'download']);

		Route::get('/search', [SearchController::class,'index']);
		Route::get('/ajax/flip/{artist_name}', [AjaxController::class,'flip']);
		Route::get('/ajax/autocomplete', [AjaxController::class,'autocomplete']);
		Route::post('/ajax/keyword', [AjaxController::class,'keyword']);

		Route::get('/ajax/artist/{artist_name}', [AjaxController::class,'artist']);
		Route::get('/ajax/artist/{artist_name}/{album_name}', [AjaxController::class,'artist_album']);
		Route::get('/ajax/artist/{artist_name}/{album_name}/{title_name}', [AjaxController::class,'artist_album_title']);

		Route::get('/artist', [SearchController::class,'index']);
		Route::get('/go/{artist_name}', [SearchController::class,'go']);
		Route::get('/artist/{artist_name}', [SearchController::class,'artist']);
		Route::get('/artist/{artist_name}/{album_name}', [SearchController::class,'artist_album']);
		Route::get('/artist/{artist_name}/{album_name}/{title_name}', [SearchController::class,'artist_album_title']);

		Route::get('/picture/{mbid}', [SearchController::class,'picture']);
		Route::get('/sonos', [SearchController::class,'sonos']);
	} else {
        $urlsOK = ["/admin", "/admin/login","/busy", "/livewire/update", "/lastfm_login'"];
        if (isset($_SERVER['REQUEST_URI']) && !in_array($_SERVER['REQUEST_URI'], $urlsOK)){
			header("location: /busy");
			exit();
		}
	}
}
