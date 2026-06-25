<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * TwoFactorController — Gestion du 2FA TOTP pour l'utilisateur connecté.
 *
 * Flux d'activation :
 *   GET  /account/two-factor        → page de setup (QR + secret affiché)
 *   POST /account/two-factor/enable → vérifier le code, activer + afficher les codes de récup.
 *   POST /account/two-factor/disable → désactiver (demande confirmation du mot de passe)
 *   POST /account/two-factor/recovery-codes → régénérer les codes de récupération
 */
class TwoFactorController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // Setup — affiche le QR code et le secret
    // ──────────────────────────────────────────────────────────────────────────

    public function show(Request $request)
    {
        $user = $request->user();

        // Générer un secret temporaire en session (non sauvegardé tant que non confirmé)
        if (!$request->session()->has('2fa_pending_secret')) {
            $request->session()->put('2fa_pending_secret', TotpService::generateSecret());
        }

        $secret = $request->session()->get('2fa_pending_secret');

        return Inertia::render('Auth/TwoFactorSetup', [
            'enabled'      => $user->hasTwoFactorEnabled(),
            'secret'       => $secret,
            'qr_url'       => TotpService::getQrUrl($secret, $user->email),
            'otp_uri'      => TotpService::getUri($secret, $user->email),
            'recovery_codes' => $user->hasTwoFactorEnabled()
                ? ($user->two_factor_recovery_codes ?? [])
                : [],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Enable — vérifie le code TOTP et active le 2FA
    // ──────────────────────────────────────────────────────────────────────────

    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'code.required' => 'Le code à 6 chiffres est requis.',
            'code.size'     => 'Le code doit contenir exactement 6 chiffres.',
            'code.regex'    => 'Le code ne doit contenir que des chiffres.',
        ]);

        $user   = $request->user();
        $secret = $request->session()->get('2fa_pending_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expirée. Rechargez la page.']);
        }

        if (!TotpService::verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Code incorrect. Vérifiez votre application d\'authentification.']);
        }

        $recoveryCodes = TotpService::generateRecoveryCodes();

        // SEC-M3: Stocker les codes de récupération sous forme de hachés SHA-256.
        // Les codes en clair sont affichés une seule fois à l'écran ($recoveryCodes)
        // mais ne sont jamais persistés tels quels.
        $hashedCodes = array_map(fn (string $c) => hash('sha256', $c), $recoveryCodes);

        $user->update([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $hashedCodes,
        ]);

        // Marquer la session comme 2FA vérifié (l'utilisateur vient de prouver possession)
        $request->session()->put('two_factor_confirmed_at', now()->timestamp);
        $request->session()->forget('2fa_pending_secret');

        return redirect()->route('account.two-factor')->with([
            'success'       => 'Double authentification activée avec succès.',
            'show_recovery' => true,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Disable — désactive le 2FA après confirmation du mot de passe
    // ──────────────────────────────────────────────────────────────────────────

    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ], [
            'password.current_password' => 'Mot de passe incorrect.',
        ]);

        $request->user()->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
        ]);

        $request->session()->forget('two_factor_confirmed_at');
        $request->session()->forget('2fa_pending_secret');

        return redirect()->route('account.two-factor')
            ->with('success', 'Double authentification désactivée.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Régénération des codes de récupération
    // ──────────────────────────────────────────────────────────────────────────

    public function regenerateCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return back()->withErrors(['error' => 'Le 2FA n\'est pas activé.']);
        }

        $codes = TotpService::generateRecoveryCodes();

        // SEC-M3: Stocker les codes hashés ; retourner les codes en clair dans la session
        // pour affichage unique — ils ne peuvent plus être relus depuis la base après ça.
        $hashedCodes = array_map(fn (string $c) => hash('sha256', $c), $codes);
        $user->update(['two_factor_recovery_codes' => $hashedCodes]);

        return redirect()->route('account.two-factor')
            ->with('success', 'Codes de récupération régénérés. Sauvegardez-les dans un endroit sûr.')
            ->with('recovery_codes_plain', $codes); // affichage unique seulement
    }
}
