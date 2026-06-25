<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTwoFactorAuthenticated — Middleware 2FA pour les routes admin/pro.
 *
 * - Si l'utilisateur n'a pas le 2FA activé → laisse passer.
 * - Si le 2FA est activé et déjà confirmé dans la session → laisse passer.
 * - Si le 2FA est activé mais non confirmé → stocke l'URL cible (intended),
 *   l'ID utilisateur, et redirige vers le défi 2FA.
 *
 * La vérification est stockée sous `two_factor_confirmed_at` (timestamp Unix).
 * Elle expire avec la session Laravel.
 */
class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pas connecté → laisser l'auth middleware gérer
        if (!$user) {
            return $next($request);
        }

        // 2FA non activé → pas de défi requis
        if (!$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // 2FA activé et déjà confirmé dans cette session → OK
        if ($request->session()->has('two_factor_confirmed_at')) {
            return $next($request);
        }

        // ── 2FA requis mais non confirmé ────────────────────────────────────
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
