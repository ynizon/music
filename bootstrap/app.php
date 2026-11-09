<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Avant StartSession : pas de fichier de session pour les visiteurs inconnus (robots)
        $middleware->prepend(\App\Http\Middleware\StatelessForUnknownVisitors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
