<?php

namespace App\Http\Middleware;

use App\Models\Ip;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowedVisitor
{
    /**
     * Accès au site : IP autorisée (table ips) ou utilisateur connecté.
     * La connexion "Se souvenir de moi" de /admin/login permet l'accès depuis n'importe quelle IP (téléphone en 4G...).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config("app.RESTRICT_IP") || auth()->check()) {
            return $next($request);
        }

        // REMOTE_ADDR est la vraie IP du client (mod_remoteip derrière le reverse proxy)
        if (in_array($request->server("REMOTE_ADDR"), Ip::pluck("ip")->all(), true)) {
            return $next($request);
        }

        return redirect("/busy");
    }
}
