<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Praxis\Core\Protection\ScrapingGuard;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège les routes qui servent le patrimoine de PraxiQuest : énoncés de
 * tests, barèmes, restitutions, pistes métiers.
 *
 * À poser sur les routes de LECTURE de contenu :
 *   Route::get('/tests/{test}', ...)->middleware('protect-content');
 *
 * Le mode 'warn' (défaut) n'alerte que — le temps de mesurer la cadence réelle
 * des candidats en production avant de passer à 'block'.
 */
class ProtectContent
{
    public function __construct(private readonly ScrapingGuard $guard)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // La détection ne doit jamais faire tomber une requête légitime :
        // cache indisponible ou table d'alertes absente se soldent par un
        // silence, pas par une 500 sur la page d'un candidat.
        try {
            $reason = $this->guard->inspect($request);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PROTECTION : détection d\'aspiration indisponible.', [
                'error' => $e->getMessage(),
            ]);

            return $next($request);
        }

        if ($reason === null) {
            return $next($request);
        }

        if (config('protection.scraping.mode') !== 'block') {
            return $next($request);
        }

        // 429 plutôt que 403 : la réponse reste vraie (« trop de requêtes »)
        // sans révéler à un concurrent le détail de ce qui l'a fait repérer.
        abort(429, 'Trop de requêtes. Si vous pensez qu\'il s\'agit d\'une erreur, écrivez à contact@praxiquest.fr');
    }
}
