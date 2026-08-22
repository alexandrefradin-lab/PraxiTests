<?php

namespace App\Http\Middleware;

use App\Support\B2c;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paywall particulier (offre B2C — config/b2c.php).
 *
 * Bloque les fonctions premium (Grimoire, PDF, plans d'action IA…) pour un
 * candidat auto-inscrit qui n'a pas acheté le Rapport complet. Inactif tant
 * que B2C_PAYWALL_ENFORCED=false. Les candidats invités par un professionnel
 * et les comptes pro/admin ne sont jamais bloqués (App\Support\B2c).
 *
 * Usage : Route::middleware('b2c.unlocked') sur les routes candidat premium.
 * Le démarrage d'épreuve a sa propre garde dans AttemptController::start()
 * (l'épreuve d'appel doit rester jouable).
 */
class EnsureB2cUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! B2c::locked($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Cette fonction fait partie du Rapport complet. Débloque ton parcours pour y accéder.');
        }

        return redirect()->route('b2c.unlock')
            ->with('info', 'Cette étape fait partie du Rapport complet. Débloque ton parcours pour continuer.');
    }
}
