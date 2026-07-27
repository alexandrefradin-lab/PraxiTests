<?php

/**
 * Filet de sécurité facturation (Phase 0 — audit 2026-07-27).
 *
 * Le chemin de revenu (Stripe) n'avait aucun test alors que la bascule LIVE est
 * imminente. Ces tests verrouillent, SANS appeler Stripe, trois garanties de
 * sécurité côté serveur :
 *  1. le webhook rejette tout événement sans signature valide (quand le secret
 *     est configuré) — l'endpoint qui octroie/retire des abonnements ;
 *  2. la sélection de plan est validée côté serveur (pas de plan arbitraire) ;
 *  3. aucun rôle « professional » n'est accordé sans abonnement actif.
 */

use App\Models\User;

// ── 1. Signature du webhook ───────────────────────────────────────────────────

it('rejette un webhook Stripe sans signature valide quand le secret est configuré', function () {
    // La sécurité du endpoint dépend entièrement de ce secret (TODO SEC-C3) :
    // s'il est vide en prod, Cashier n'attache pas VerifyWebhookSignature et
    // n'importe qui peut forger des événements d'abonnement.
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $this->postJson('/stripe/webhook', [
        'id'   => 'evt_forged',
        'type' => 'customer.subscription.created',
    ])->assertForbidden(); // 403 : signature Stripe absente/invalide
})->skip(
    ! class_exists(\Laravel\Cashier\Http\Controllers\WebhookController::class),
    'Cashier non installé dans cet environnement.'
);

// ── 2. Validation serveur de la sélection de plan ─────────────────────────────

it('checkout refuse un plan inconnu (validation serveur)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('billing.checkout'), ['plan' => 'plan_pirate', 'period' => 'monthly'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

it('checkout refuse une période invalide', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('billing.checkout'), ['plan' => array_key_first(config('plans.plans')), 'period' => 'hebdo'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('period');
});

// ── 3. Pas de rôle pro sans paiement ──────────────────────────────────────────

it("n'attribue pas le rôle professional à un utilisateur sans abonnement actif", function () {
    $user = User::factory()->create(); // aucun abonnement Stripe

    $this->actingAs($user)
        ->get(route('billing.success'))
        ->assertOk();

    expect($user->fresh()->hasRole('professional'))->toBeFalse();
});
