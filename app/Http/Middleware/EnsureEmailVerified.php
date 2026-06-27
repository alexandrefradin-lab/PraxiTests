<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantit que l'utilisateur a vérifié son adresse email avant d'accéder
 * aux routes protégées (tests, résultats, billing…).
 *
 * Remplace l'alias 'verified' par défaut de Laravel pour ajouter :
 *   1. Un kill-switch (config praxiquest.security.require_email_verification)
 *      afin de pouvoir désactiver le gate si le SMTP de prod tombe, sans
 *      redéployer de code.
 *   2. Une exemption pour les comptes staff (admin / super-admin) qui sont
 *      créés via seeder et ne passent pas par le flux d'inscription public.
 *
 * Comportement identique au middleware natif sinon : redirection vers
 * 'verification.notice' (ou 403 JSON pour les requêtes API).
 */
class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response
    {
        // Kill-switch : gate désactivé → on laisse passer.
        if (! config('praxiquest.security.require_email_verification', true)) {
            return $next($request);
        }

        $user = $request->user();

        // Comptes staff exemptés (créés par seeder, déjà de confiance).
        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super-admin'])) {
            return $next($request);
        }

        if (! $user
            || ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())) {

            if ($request->expectsJson()) {
                abort(409, "Votre adresse email n'a pas encore été vérifiée.");
            }

            return redirect()->guest(
                $redirectToRoute ? route($redirectToRoute) : route('verification.notice')
            );
        }

        return $next($request);
    }
}
