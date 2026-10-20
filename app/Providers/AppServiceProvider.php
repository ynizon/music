<?php

namespace App\Providers;

use Aerni\Spotify\Spotify;
use App\Api\Spotify as SpotifyUp;
use App\Models\Spotdl;
use App\Observers\SpotdlObserver;
use Illuminate\Support\Facades\URL;
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
        // En prod, le TLS est terminé par le reverse proxy du NAS : on force les URLs en https
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Spotdl::observe(SpotdlObserver::class);
    }
}
