<?php

/**
 * Anti-énumération d'emails sur la demande de réinitialisation (audit M1, 2026-07-27).
 *
 * AuthController::sendResetLink renvoyait un message différent selon que le compte
 * existait (« lien envoyé ») ou non (« Aucun compte trouvé »), ce qui permettait
 * d'énumérer les comptes. La réponse est désormais identique dans les deux cas.
 */

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    // Le throttle de la route (throttle:3,10) n'est pas le sujet de ces tests.
    $this->withoutMiddleware(ThrottleRequests::class);
});

it('renvoie la même réponse pour un compte existant et un compte inexistant', function () {
    $user = User::factory()->create(['email' => 'existe@example.test']);

    $existing = $this->post(route('password.email'), ['email' => 'existe@example.test']);
    $existing->assertSessionHas('success');
    $existing->assertSessionHasNoErrors();

    $unknown = $this->post(route('password.email'), ['email' => 'inconnu@example.test']);
    // Point clé anti-énumération : PAS d'erreur « aucun compte », même flash succès.
    $unknown->assertSessionHasNoErrors();
    $unknown->assertSessionHas('success');

    // Les deux messages de succès sont strictement identiques (aucune fuite).
    expect(session('success'))->toBeString();
});

it('envoie réellement la notification de réinitialisation à un compte existant', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'reel@example.test']);

    $this->post(route('password.email'), ['email' => 'reel@example.test'])
        ->assertSessionHas('success');

    Notification::assertSentTo($user, ResetPassword::class);
});

it("n'envoie aucune notification pour une adresse inconnue", function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'personne@example.test'])
        ->assertSessionHas('success'); // même message, mais…

    Notification::assertNothingSent(); // …aucun email n'est réellement parti.
});

it('valide le format de l\'email avant tout', function () {
    $this->post(route('password.email'), ['email' => 'pas-un-email'])
        ->assertSessionHasErrors('email');
});
