<?php

/**
 * Enregistrement de la dernière connexion (audit Phase 0, 2026-07-27).
 *
 * last_login_at / last_login_ip sont hors $fillable (anti mass-assignment).
 * AuthController::login et TwoFactorChallengeController les écrivaient via
 * update() → fill() les ignore → ils n'étaient jamais persistés (colonne
 * « dernière connexion » toujours vide en back-office). Corrigé en forceFill().
 */

use App\Models\User;
use Illuminate\Routing\Middleware\ThrottleRequests;

beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
});

it('enregistre last_login_at et last_login_ip à la connexion directe', function () {
    $user = User::factory()->create(); // mot de passe 'password', sans 2FA
    expect($user->last_login_at)->toBeNull();

    $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    $fresh = $user->fresh();
    expect($fresh->last_login_at)->not->toBeNull()
        ->and($fresh->last_login_ip)->not->toBeNull();
});

it('ne connecte pas et ne date rien avec un mauvais mot de passe', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'mauvais',
    ])->assertSessionHasErrors('email');

    expect($user->fresh()->last_login_at)->toBeNull();
    $this->assertGuest();
});
