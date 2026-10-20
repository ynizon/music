<?php

use App\Http\Middleware\AllowedVisitor;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpotController;
use App\Http\Controllers\SonosCustomController;

// Pages publiques (/admin et /livewire sont déclarées par Filament et Livewire)
Route::get('/busy', [HomeController::class,'busy']);
Route::get('/cron', [SpotController::class,'cron']);

// Le reste du site : IP autorisée ou utilisateur connecté (voir AllowedVisitor)
Route::middleware(AllowedVisitor::class)->group(function () {
    Route::get('/home', [HomeController::class,'index'])->name('home');
    Route::get('/lastfm_login', [HomeController::class,'lastfm_login']);
    Route::post('/lastfm_login', [HomeController::class,'lastfm_login']);

    Route::post('/save', [SpotController::class,'save']);

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
    Route::get('/sonos', [SonosCustomController::class, 'index']);
    Route::get('/sonos/autocomplete', [SonosCustomController::class, 'autocomplete']);
    Route::get('/sonos/albums', [SonosCustomController::class, 'albums']);
    Route::post('/sonos/play', [SonosCustomController::class, 'play']);
    Route::post('/sonos/control', [SonosCustomController::class, 'control']);
    Route::get('/sonos/favorites', [SonosCustomController::class, 'favorites']);
    Route::post('/sonos/favorites/toggle', [SonosCustomController::class, 'toggleFavorite']);

    Route::get('/franceinfo', [SearchController::class,'franceinfo']);
});
