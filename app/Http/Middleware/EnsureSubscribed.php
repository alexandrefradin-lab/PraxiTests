<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque l'accès aux routes protégées si l'utilisateur n'a pas
 * d'abonnement actif (ni trial en cours).
 *
 * Usage dans les routes :
 *   Route::middleware(['auth', 'subscribed'])->group(...)
 */
class EnsureSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins exemptés (cf. audit M-2 : hasRole('a|b') ne fonctionne pas avec
        // Spatie — il faut hasAnyRole([...]), sinon aucun admin n'était jamais exempté).
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return $next($request);
        }

        // Trial ou abonnement actif → OK
        if ($user->onTrial('default') || $user->subscribed('default')) {
            return $next($request);
        }

        // Sinon → page des plans
        return redirect()->route('billing.plans')
            ->with('warning', 'Un abonnement actif est requis pour accéder à cette section.');
    }
}
