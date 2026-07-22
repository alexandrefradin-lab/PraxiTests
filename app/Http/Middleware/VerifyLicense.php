<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Praxis\Core\Protection\LicenseService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuse de servir l'application si le code tourne hors licence.
 *
 * Placé en tête du groupe web : une instance pirate ne doit même pas afficher
 * la page d'accueil. Le mode 'warn' (défaut) se contente de journaliser, le
 * temps de vérifier en production qu'aucune configuration légitime ne tombe
 * dans les mailles — on ne bascule sur 'block' qu'ensuite.
 *
 * Bascule : PRAXIQUEST_LICENSE_ENFORCED=true puis PRAXIQUEST_LICENSE_MODE=block
 * dans le .env, suivi de `php artisan config:cache`.
 */
class VerifyLicense
{
    public function __construct(private readonly LicenseService $license)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('protection.license.enabled')) {
            return $next($request);
        }

        // Health-check, webhook Stripe et pages légales restent servis même
        // hors licence : les couper créerait plus de dégâts que de protection.
        if ($request->is(...(array) config('protection.license.exempt_paths', []))) {
            return $next($request);
        }

        $status = $this->license->status($request->getHost());

        if ($status->allowsExecution()) {
            return $next($request);
        }

        $this->report($request, $status);

        if (config('protection.license.mode') !== 'block') {
            return $next($request);
        }

        // 503 plutôt que 403 : le service n'est pas refusé à l'utilisateur, il
        // est indisponible sur cette installation. Aucun détail technique n'est
        // exposé — inutile d'indiquer au copieur ce qu'il doit contourner.
        abort(503, "Cette installation de PraxiQuest n'est pas autorisée à fonctionner sur ce domaine. Contactez l'éditeur : contact@praxiquest.fr");
    }

    /**
     * Journalise l'anomalie. Une tentative de copie (domaine hors licence ou
     * signature contrefaite) part en `critical` pour ressortir dans les alertes,
     * une licence simplement expirée en `warning`.
     */
    private function report(Request $request, \Praxis\Core\Protection\LicenseStatus $status): void
    {
        $context = [
            'status'   => $status->status,
            'host'     => $status->host,
            'reason'   => $status->reason,
            'licensee' => $status->licensee(),
            'ip'       => $request->ip(),
            'path'     => $request->path(),
            'agent'    => substr((string) $request->userAgent(), 0, 200),
        ];

        if ($status->looksLikeCopy()) {
            Log::critical('PROTECTION : exécution de PraxiQuest hors licence — copie probable.', $context);
            return;
        }

        // Une expiration ne se journalise qu'une fois par heure, sinon chaque
        // requête inonde le fichier de log jusqu'au renouvellement.
        \Illuminate\Support\Facades\Cache::remember(
            'protection:license:reported:' . $status->host,
            3600,
            function () use ($context) {
                Log::warning('PROTECTION : licence PraxiQuest non valide.', $context);
                return true;
            }
        );
    }
}
