<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Flux de vérification d'adresse email (candidats issus de l'inscription libre).
 *
 * Routes attendues (cf. routes/auth.php) — les noms sont imposés par la
 * notification native Illuminate\Auth\Notifications\VerifyEmail :
 *   - verification.notice  GET  /email/verify
 *   - verification.verify  GET  /email/verify/{id}/{hash}   (signed)
 *   - verification.send    POST /email/verification-notification
 */
class EmailVerificationController extends Controller
{
    /** Page « Vérifiez votre boîte mail ». */
    public function notice(Request $request): RedirectResponse|Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('onboarding.show'));
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email'  => $request->user()->email,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Cible du lien signé reçu par email. EmailVerificationRequest valide
     * automatiquement la signature + le hash de l'email et autorise l'accès.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('onboarding.show') . '?verified=1');
        }

        $request->fulfill(); // marque l'email comme vérifié + déclenche l'événement Verified

        // Tunnel PDV /structures : reprendre le paiement là où il s'était arrêté.
        if ($request->session()->has('subscribe_intent')) {
            return redirect()->route('billing.subscribe')
                ->with('success', 'Votre adresse email a bien été confirmée.');
        }

        return redirect()->intended(route('onboarding.show') . '?verified=1')
            ->with('success', 'Votre adresse email a bien été confirmée. Bienvenue dans la Quête !');
    }

    /** Renvoie un nouvel email de vérification. */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('onboarding.show'));
        }

        // Encadré par un try/catch : si le SMTP est indisponible, on ne renvoie
        // pas une 500 mais un message d'erreur exploitable par l'utilisateur.
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', "L'envoi a échoué. Réessayez dans quelques instants ou contactez le support.");
        }

        return back()->with('status', 'verification-link-sent');
    }
}
