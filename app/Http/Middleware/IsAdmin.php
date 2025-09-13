<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // On vérifie si l'utilisateur est authentifié et si le champ 'admin' est vrai
        if (auth()->check() && auth()->user()->isAdmin()) {
            // Si la condition est vraie, on passe la requête à la suite
            return $next($request);
        }

        // Sinon, on renvoie une erreur "403 Forbidden" (Accès non autorisé)
        auth()->logout();
        abort(403, 'Accès non autorisé.');
    }
}