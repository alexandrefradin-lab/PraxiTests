<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\TestInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Valider les identifiants sans créer de session (pour intercepter si 2FA requis)
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Auth::validate($data)) {
            return back()->withErrors(['email' => 'Identifiants invalides.']);
        }

        // ── 2FA requis pour admin/pro avec 2FA activé ──────────────────────
        if ($user->hasTwoFactorEnabled() && ($user->hasRole('admin') || $user->hasRole('professional'))) {
            $request->session()->put('two_factor_user_id', $user->id);
            $request->session()->put('two_factor_remember', $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('two-factor.challenge');
        }

        // ── Connexion directe (2FA non activé) ────────────────────────────
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);

        return redirect()->intended(route('home'));
    }

    public function showRegister(Request $request)
    {
        return Inertia::render('Auth/Register', [
            'email' => filter_var($request->query('email'), FILTER_VALIDATE_EMAIL) ?: null,
            // SEC-M12 / RGPD : inscription via invitation → proposer la case de
            // consentement au partage des résultats avec le professionnel invitant.
            'viaInvitation' => $request->session()->has('invitation_token'),
        ]);
    }

    /** Version des CGU actuellement en vigueur — à incrémenter à chaque nouvelle version */
    private const CGU_VERSION = '1.1';

    public function register(Request $request)
    {
        // Honeypot anti-bot (SEC) : le champ 'website' est invisible pour les
        // humains (masqué en CSS) mais rempli par la plupart des bots. S'il est
        // renseigné, on abandonne silencieusement sans créer de compte ni
        // révéler la raison (le bot croit à un simple retour de page).
        if (filled($request->input('website'))) {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'terms'       => ['required', 'accepted'],
            // RGPD : consentement au partage des résultats avec le professionnel
            // invitant. Libre (nullable) — refuser n'empêche pas l'inscription.
            'consent_share' => ['nullable', 'boolean'],
            'quest_title' => ['required', 'in:architecte,explorateur,passeur'],
        ], [
            'terms.required'        => 'Vous devez accepter les Conditions Générales d\'Utilisation.',
            'terms.accepted'        => 'Vous devez accepter les Conditions Générales d\'Utilisation.',
            'quest_title.required'  => 'Choisis ton titre de Héros pour commencer la Quête.',
            'quest_title.in'        => 'Titre de Héros invalide.',
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'terms_accepted_at' => now(),
            'terms_version'     => self::CGU_VERSION,
        ]);

        // Crée le profil minimal avec le titre de Héros choisi à l'inscription.
        // L'onboarding complètera les autres champs (statut, CV…) via updateOrCreate.
        Profile::create([
            'user_id'     => $user->id,
            'quest_title' => $data['quest_title'],
        ]);

        // CRM : chaque inscrit entre dans le pipeline des leads (source « inscription »).
        // Si un lead existe déjà pour cet email (prospect importé, campagne…), on le
        // rattache au compte sans écraser sa source ni son statut — le listener RIASEC
        // (praximet) l'upgradera en « qualified » à la fin du test phare.
        // Ne doit jamais faire échouer l'inscription.
        try {
            $lead = \App\Models\Lead::firstOrNew(['email' => $user->email]);
            if (! $lead->exists) {
                $lead->source = 'inscription';
                $lead->status = 'new';
                $lead->score  = 20;
            }
            $lead->user_id          = $user->id;
            $lead->first_name       = $lead->first_name ?: $user->name;
            $lead->last_activity_at = now();
            $lead->save();
        } catch (\Throwable $e) {
            report($e);
        }

        // L'événement Registered déclenche l'envoi du mail de vérification (queue sync).
        // Si le SMTP est indisponible/mal configuré (OVH), l'exception ne doit PAS
        // faire échouer l'inscription (sinon 500 sur /register). On la journalise.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            report($e);
        }

        // Vérification auto uniquement en local/testing (SEC-M6).
        // En production avec mailer=log (ex. OVH sans SMTP), les emails ne sont
        // PAS auto-vérifiés pour forcer la mise en place d'un vrai flux de vérification.
        if (app()->environment(['local', 'testing'])) {
            $user->markEmailAsVerified();
        }

        Auth::login($user);

        // BUG-3 — consommer le token d'invitation stocké par InvitationController::land()
        if ($token = session()->pull('invitation_token')) {
            $invitation = TestInvitation::where('token', $token)
                ->whereNotIn('status', ['expired', 'completed'])
                ->first();
            if ($invitation) {
                $invitation->update(array_merge(
                    ['status' => 'started'],
                    // SEC-M12 / RGPD : consentement de partage horodaté (preuve).
                    $request->boolean('consent_share') ? [
                        'consent_share_professional' => true,
                        'consent_given_at'           => now(),
                    ] : [],
                ));
                // Garder l'ID pour qu'AttemptController puisse le lier à la tentative
                session(['pending_invitation_id' => $invitation->id]);
            }
        }

        return redirect()->route('onboarding.show');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    // ─── Reset password ───────────────────────────────────────────────────────

    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Un lien de réinitialisation a été envoyé à ' . $request->email)
            : back()->withErrors(['email' => 'Aucun compte trouvé avec cette adresse email.']);
    }

    public function showResetForm(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->update(['password' => Hash::make($password)]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Mot de passe réinitialisé avec succès.')
            : back()->withErrors(['email' => 'Ce lien de réinitialisation est invalide ou expiré.']);
    }
}
