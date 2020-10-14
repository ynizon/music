<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('/maintenance', 'MaintenanceController@index');
Route::get('/maintenance/admin', 'MaintenanceController@admin');
Route::post('/maintenance/admin', 'MaintenanceController@admin');
Route::get('/busy', 'HomeController@busy');
Route::get('/home', 'HomeController@index')->name('home');

if (config("app.ONLY_IP") == "" or in_array($_SERVER['REMOTE_ADDR'],config("app.ONLY_IP"))){
	Route::get('/', 'HomeController@index');
	Route::get('/sitemap.xml', 'HomeController@sitemap');
	Route::get('/contact', 'HomeController@contact');
	Route::get('/faq', 'HomeController@faq');
	Route::get('/lastfm_login', 'HomeController@lastfm_login');
	Route::post('/lastfm_login', 'HomeController@lastfm_login');

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
}else{
	if ($_SERVER['REQUEST_URI'] != "/busy"){
		header("location: /busy");
		exit();
	}
}
//On filtre par permission
Route::group(['middleware' => ['auth','permission:user-edit']], function () {	
		Route::resource('users', 'UserController');
});



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
