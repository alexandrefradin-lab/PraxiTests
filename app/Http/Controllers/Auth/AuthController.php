<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        if (!Auth::attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Identifiants invalides.']);
        }

        $request->session()->regenerate();
        Auth::user()->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);

        return redirect()->intended(route('home'));
    }

    public function showRegister(Request $request)
    {
        return Inertia::render('Auth/Register', ['email' => $request->query('email')]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        // Vérification auto en développement / si aucun driver mail réel configuré
        if (app()->environment('local', 'testing') || config('mail.mailer') === 'log' || config('mail.mailer') === 'array') {
            $user->markEmailAsVerified();
        }

        Auth::login($user);

        // BUG-3 — consommer le token d'invitation stocké par InvitationController::land()
        if ($token = session()->pull('invitation_token')) {
            $invitation = TestInvitation::where('token', $token)
                ->whereNotIn('status', ['expired', 'completed'])
                ->first();
            if ($invitation) {
                $invitation->update(['status' => 'started']);
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
