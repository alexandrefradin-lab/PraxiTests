<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * TwoFactorChallengeController — Défi 2FA.
 *
 * Gère deux scénarios :
 *
 *   A) Login initial avec 2FA :
 *      AuthController::login() ne connecte PAS l'utilisateur → stocke
 *      two_factor_user_id en session → redirige ici. Après vérification,
 *      le contrôleur appelle Auth::loginUsingId() pour compléter la connexion.
 *
 *   B) Session admin existante sans confirmation 2FA :
 *      EnsureTwoFactorAuthenticated stocke two_factor_user_id + url.intended
 *      → redirige ici. L'utilisateur EST déjà auth, on se contente de marquer
 *      two_factor_confirmed_at dans la session.
 *
 * GET  /two-factor-challenge → formulaire de saisie
 * POST /two-factor-challenge → vérification TOTP ou code de récupération
 */
class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        // Si l'utilisateur est authentifié et a déjà confirmé le 2FA, le rediriger
        if (Auth::check() && $request->session()->has('two_factor_confirmed_at')) {
            return redirect()->intended(route('home'));
        }

        // Doit avoir un pending 2FA (scénario A ou B)
        if (!$request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'recovery' => (bool) $request->query('recovery', false),
        ]);
    }

    public function verify(Request $request)
    {
        // Récupérer l'utilisateur depuis la session (scénario A ET B)
        $userId = $request->session()->get('two_factor_user_id');

        // Scénario B : déjà auth mais two_factor_user_id pas encore en session
        // (ex : accès direct à /two-factor-challenge sans passer par le middleware)
        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);
            return redirect()->route('login');
        }

        // ── Vérification du code ─────────────────────────────────────────────
        $isRecovery = $request->filled('recovery_code');

        if ($isRecovery) {
            $request->validate([
                'recovery_code' => ['required', 'string'],
            ], ['recovery_code.required' => 'Le code de récupération est requis.']);

            if (!$user->useRecoveryCode(strtoupper(trim($request->recovery_code)))) {
                return back()->withErrors(['recovery_code' => 'Code de récupération invalide ou déjà utilisé.']);
            }
        } else {
            $request->validate([
                'code' => ['required', 'string'],
            ], ['code.required' => 'Le code à 6 chiffres est requis.']);

            if (!TotpService::verify($user->two_factor_secret, $request->code)) {
                return back()->withErrors(['code' => 'Code incorrect. Vérifiez votre application d\'authentification.']);
            }
        }

        // ── Authentification complète ────────────────────────────────────────

        // Scénario A : pas encore connecté → connecter maintenant
        $wasAlreadyAuthenticated = Auth::check();
        if (!$wasAlreadyAuthenticated) {
            $remember = $request->session()->get('two_factor_remember', false);
            Auth::loginUsingId($userId, $remember);
            $request->session()->regenerate();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
        }

        // Marquer le 2FA comme confirmé dans cette session
        $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);
        $request->session()->put('two_factor_confirmed_at', now()->timestamp);

        return redirect()->intended(route('home'));
    }
}
