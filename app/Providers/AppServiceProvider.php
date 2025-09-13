<?php

namespace App\Providers;

use Aerni\Spotify\Spotify;
use App\Models\SpotifyUp;
use App\Policies\UserPolicy;
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
            return new SpotifyUp($defaultConfig);
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
