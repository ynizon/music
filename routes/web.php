<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/maintenance', 'MaintenanceController@index');
Route::get('/maintenance/admin', 'MaintenanceController@admin');
Route::post('/maintenance/admin', 'MaintenanceController@admin');
Route::get('/busy', 'HomeController@busy');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/lastfm_login', 'HomeController@lastfm_login');
Route::post('/lastfm_login', 'HomeController@lastfm_login');

//echo var_dump($_SESSION);exit();
$allowedIps = config("app.ONLY_IP");
if (file_exists(storage_path("ips.txt"))){
    $ips = json_decode(file_get_contents(storage_path("ips.txt")), true);
    $allowedIps = array_merge($ips,$allowedIps);
}
if (isset($_SESSION["addip"])){
	$allowedIps = array_merge([$_SESSION['addip']],$allowedIps);
}

Route::get('/admin', 'HomeController@admin');
Route::post('/admin', 'HomeController@admin');
if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$allowedIps)){
	Route::get('/', 'HomeController@index');
	Route::get('/sitemap.xml', 'HomeController@sitemap');
	Route::get('/contact', 'HomeController@contact');
	Route::get('/faq', 'HomeController@faq');
	Route::get('/download', 'HomeController@download');

	Route::get('/search', 'SearchController@index');
	Route::get('/ajax/flip/{artist_name}', 'AjaxController@flip');
	Route::get('/ajax/autocomplete', 'AjaxController@autocomplete');
	Route::post('/ajax/keyword', 'AjaxController@keyword');

	Route::get('/ajax/artist/{artist_name}', 'AjaxController@artist');
	Route::get('/ajax/artist/{artist_name}/{album_name}', 'AjaxController@artist_album');
	Route::get('/ajax/artist/{artist_name}/{album_name}/{title_name}', 'AjaxController@artist_album_title');

	Route::get('/artist', 'SearchController@index');
	Route::get('/artist/{artist_name}', 'SearchController@artist');
	Route::get('/artist/{artist_name}/{album_name}', 'SearchController@artist_album');
	Route::get('/artist/{artist_name}/{album_name}/{title_name}', 'SearchController@artist_album_title');

	Route::get('/picture/{mbid}', 'SearchController@picture');
    Route::get('/sonos', 'SearchController@sonos');
    Route::get('/checkipsonos', 'SearchController@checkipsonos');
}else{
	if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "/admin" && $_SERVER['REQUEST_URI'] != "/busy"
        && $_SERVER['REQUEST_URI'] != "/lastfm_login"){
        header("location: /busy");
		exit();
	}
}
//On filtre par permission
Route::group(['middleware' => ['auth','permission:user-edit']], function () {
		Route::resource('users', 'UserController');
});

Route::get('/home', 'HomeController@index')->name('home');

require __DIR__.'/auth.php';
