<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PasswordController — Changement de mot de passe pour l'utilisateur connecté.
 *
 *   GET /account/password → formulaire (mot de passe actuel + nouveau ×2)
 *   PUT /account/password → validation et mise à jour
 *
 * Distinct du flux « Sceau oublié ? » (reset par email, routes guest) : ici
 * l'utilisateur est connecté et doit confirmer son mot de passe actuel.
 */
class PasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Auth/PasswordChange');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required'         => 'Votre mot de passe actuel est requis.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required'  => 'Le nouveau mot de passe est requis.',
            'password.min'       => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
            'password.different' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'Votre sceau secret a été mis à jour.');
    }
}
