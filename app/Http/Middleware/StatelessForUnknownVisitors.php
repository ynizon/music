<?php

namespace App\Http\Middleware;

use App\Models\Ip;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware global, exécuté avant StartSession.
 *
 * Avec RESTRICT_IP, les robots (souvent déguisés en navigateurs) sont redirigés vers /busy,
 * mais chaque requête créait un fichier de session : des dizaines de milliers de fichiers,
 * et un ramasse-miettes de sessions qui parcourt tout le dossier, ce qui fait gratter le disque.
 *
 * Pour un visiteur inconnu (IP non autorisée, sans cookie de session ni « se souvenir de moi »),
 * la session est gardée en mémoire (driver array) : rien n'est écrit sur le disque.
 * Les visiteurs autorisés et les utilisateurs connectés gardent la session fichier.
 */
class StatelessForUnknownVisitors
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.RESTRICT_IP') && $this->isUnknownVisitor($request)) {
            config(['session.driver' => 'array']);
        }

        return $next($request);
    }

    private function isUnknownVisitor(Request $request): bool
    {
        // Connexion à l'administration (Filament / Livewire) : session fichier indispensable
        if ($request->is('admin', 'admin/*', 'livewire/*', 'filament/*')) {
            return false;
        }

        // Visiteur ayant déjà une session ou un cookie « se souvenir de moi »
        if ($request->cookies->has(config('session.cookie'))) {
            return false;
        }
        foreach (array_keys($request->cookies->all()) as $name) {
            if (Str::startsWith($name, 'remember_')) {
                return false;
            }
        }

        // REMOTE_ADDR est la vraie IP du client (mod_remoteip derrière le reverse proxy)
        return !in_array($request->server('REMOTE_ADDR'), Ip::pluck('ip')->all(), true);
    }
}
