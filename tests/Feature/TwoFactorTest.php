<?php

/**
 * Parcours 2FA — régression du bug de persistance (audit Phase 0, 2026-07-27).
 *
 * `two_factor_secret` / `two_factor_recovery_codes` sont hors $fillable (protection
 * anti mass-assignment). Or `TwoFactorController` et `User::useRecoveryCode`
 * persistaient via update()/updateQuietly() → fill() ignore silencieusement ces
 * champs → le secret n'était jamais enregistré (2FA inactif) et les codes de
 * récupération n'étaient jamais consommés (réutilisables). Corrigé en forceFill().
 *
 * Ces tests exercent le VRAI parcours (endpoints + code TOTP réel) pour verrouiller
 * la persistance et empêcher toute régression.
 */

use App\Models\User;
use App\Services\TotpService;

/** Calcule le code TOTP courant pour un secret donné (hotp est privé → réflexion). */
function totpCodeFor(string $secret): string
{
    $m = new ReflectionMethod(TotpService::class, 'hotp');
    $m->setAccessible(true);
    return $m->invoke(null, $secret, intdiv(time(), 30));
}

it("active le 2FA et persiste réellement le secret via l'endpoint enable", function () {
    $user   = User::factory()->create();
    $secret = TotpService::generateSecret();

    $this->actingAs($user)
        ->withSession(['2fa_pending_secret' => $secret])
        ->post(route('account.two-factor.enable'), ['code' => totpCodeFor($secret)])
        ->assertRedirect(route('account.two-factor'))
        ->assertSessionHas('success');

    $fresh = $user->fresh();
    expect($fresh->hasTwoFactorEnabled())->toBeTrue()
        ->and($fresh->two_factor_secret)->toBe($secret)          // cast encrypted → déchiffré
        ->and($fresh->two_factor_recovery_codes)->toHaveCount(8); // codes de récup persistés
});

it('refuse un code TOTP invalide et ne persiste rien', function () {
    $user   = User::factory()->create();
    $secret = TotpService::generateSecret();

    $this->actingAs($user)
        ->withSession(['2fa_pending_secret' => $secret])
        ->post(route('account.two-factor.enable'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

it('désactive le 2FA et persiste la désactivation', function () {
    $user = User::factory()->create();
    // 2FA actif au départ (forceFill : champs hors $fillable).
    $user->forceFill([
        'two_factor_secret'         => TotpService::generateSecret(),
        'two_factor_recovery_codes' => [hash('sha256', 'ABCD-1234')],
    ])->save();
    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();

    $this->actingAs($user)
        ->post(route('account.two-factor.disable'), ['password' => 'password'])
        ->assertRedirect(route('account.two-factor'));

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse()
        ->and($user->fresh()->two_factor_recovery_codes)->toBeNull();
});
