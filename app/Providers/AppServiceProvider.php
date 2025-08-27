<?php

namespace App\Providers;

use Aerni\Spotify\Spotify;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Spotify::class, function ($app) {
            $defaultConfig = [
                'country' => config('spotify.default_config.country'),
                'locale' => config('spotify.default_config.locale'),
                'market' => config('spotify.default_config.market'),
            ];
            return new \App\Models\SpotifyUp($defaultConfig);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
