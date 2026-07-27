<?php

/**
 * 2FA obligatoire pour les administrateurs (finding M3 / SEC-M5, audit 27/07/2026).
 *
 * Constat de l'audit : « le 2FA reste opt-in pour les admins ». En réalité le
 * middleware EnsureTwoFactorAuthenticated force déjà l'activation (config
 * praxiquest.security.admin_2fa_required, défaut true), mais AUCUN test ne
 * verrouillait ce comportement — une régression pouvait le désactiver en silence.
 *
 * Ces tests exercent le middleware sur une route admin réelle ; la redirection se
 * produit DANS le middleware, avant le contrôleur (pas de rendu de dashboard).
 */

use App\Models\User;
use Spatie\Permission\Models\Role;

function makeAdmin(array $attrs = []): User
{
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create($attrs); // email vérifié via la factory
    $admin->assignRole('admin');

    return $admin;
}

it('force un admin sans 2FA à activer le 2FA avant d\'accéder au back-office', function () {
    $admin = makeAdmin(); // pas de two_factor_secret

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('account.two-factor')); // redirigé vers l'activation
});

it('exige la confirmation du défi 2FA pour un admin qui a activé le 2FA', function () {
    $admin = makeAdmin();
    // 2FA activé (forceFill : champ hors $fillable) mais session NON confirmée.
    $admin->forceFill(['two_factor_secret' => 'JBSWY3DPEHPK3PXP'])->saveQuietly();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('two-factor.challenge')); // défi requis, pas le setup
});

// NB : le kill-switch praxiquest.security.admin_2fa_required=false (défaut true)
// débraie cette obligation. Non testé ici pour ne pas dépendre du rendu du
// dashboard admin (SQL brut non couvert sous SQLite) ; le défaut sécurisé (true)
// est ce qui compte et il est verrouillé par les deux tests ci-dessus.
