<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTwoFactorAuthenticated — Middleware 2FA pour les routes admin/pro.
 *
 * - Si l'utilisateur n'a pas le 2FA activé → laisse passer.
 *   Exception (SEC-M5) : si l'utilisateur est admin et n'a PAS configuré le 2FA,
 *   il est redirigé vers la page d'activation obligatoire.
 * - Si le 2FA est activé et déjà confirmé dans la session → laisse passer,
 *   sauf si la confirmation a expiré (> 8 heures) (SEC-M4).
 * - Si le 2FA est activé mais non confirmé → stocke l'URL cible (intended),
 *   l'ID utilisateur, et redirige vers le défi 2FA.
 *
 * La vérification est stockée sous `two_factor_confirmed_at` (timestamp Unix).
 */
class EnsureTwoFactorAuthenticated
{
    /** Durée de validité de la confirmation 2FA en secondes (8 heures). SEC-M4 */
    private const TWO_FACTOR_TTL = 8 * 3600;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pas connecté → laisser l'auth middleware gérer
        if (!$user) {
            return $next($request);
        }

        // SEC-M5 : Admin sans 2FA configuré → forcer l'activation
        if ($user->hasRole('admin') && !$user->hasTwoFactorEnabled()) {
            // Ne pas boucler si on est déjà sur la page d'activation
            if (!$request->routeIs('two-factor.setup', 'two-factor.enable', 'logout')) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Two factor authentication setup required for administrators.'], 403);
                }
                return redirect()->route('two-factor.setup')
                    ->with('warning', 'L\'authentification à deux facteurs est obligatoire pour les administrateurs.');
            }
            return $next($request);
        }

        // 2FA non activé (non-admin) → pas de défi requis
        if (!$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // SEC-M4 : 2FA activé et confirmé → vérifier l'expiration (8h)
        if ($request->session()->has('two_factor_confirmed_at')) {
            $confirmedAt = $request->session()->get('two_factor_confirmed_at');
            if (is_numeric($confirmedAt) && (time() - $confirmedAt) < self::TWO_FACTOR_TTL) {
                return $next($request);
            }
            // Expirée : purger la confirmation et redemander le défi
            $request->session()->forget('two_factor_confirmed_at');
        }

        // ── 2FA requis mais non confirmé (ou expiré) ─────────────────────────
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Two factor authentication required.'], 423);
        }

        // Conserver l'URL cible pour redirect()->intended() après vérification
        $request->session()->put('url.intended', $request->fullUrl());
        // Conserver l'ID pour que le challenge puisse identifier l'utilisateur
        $request->session()->put('two_factor_user_id', $user->id);

        return redirect()->route('two-factor.challenge');
    }
}
