<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Praxis\Core\Protection\DeviceGuard;
use Symfony\Component\HttpFoundation\Response;

/**
 * Surveille le partage / la revente d'accès professionnels.
 *
 * Posé sur le groupe web : n'agit que sur les comptes authentifiés portant un
 * rôle surveillé (cf. protection.sharing.watched_roles), et n'écrit en base
 * qu'une fois par appareil et par quart d'heure.
 *
 * En mode 'block', le compte n'est pas déconnecté sèchement — on le renvoie
 * vers une page d'explication. Couper un organisme en pleine session de tests
 * ferait plus de dégâts commerciaux que le partage lui-même.
 */
class TrackDevice
{
    public function __construct(private readonly DeviceGuard $guard)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // La détection ne doit jamais faire tomber une requête légitime :
        // une table absente ou un cache indisponible se solde par un silence.
        try {
            $anomaly = $this->guard->inspect($request);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PROTECTION : détection de partage indisponible.', [
                'error' => $e->getMessage(),
            ]);

            return $next($request);
        }

        if ($anomaly === null || config('protection.sharing.mode') !== 'block') {
            return $next($request);
        }

        // Les requêtes Inertia/XHR ne savent pas suivre une redirection HTML :
        // on leur répond en 403 pour que le front affiche l'erreur.
        if ($request->header('X-Inertia') || $request->expectsJson()) {
            abort(403, 'Ce compte est utilisé depuis un nombre inhabituel d\'appareils. Contactez contact@praxiquest.fr');
        }

        return redirect()->route('billing.plans')->with(
            'warning',
            'Ce compte est utilisé depuis un nombre inhabituel d\'appareils. Un abonnement couvre une structure : contactez-nous pour ajouter des accès.'
        );
    }
}
