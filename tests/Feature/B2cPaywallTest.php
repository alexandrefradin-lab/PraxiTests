<?php

/**
 * Paywall particulier (offre B2C — config/b2c.php).
 *
 * Verrouille les garanties du modèle « Rapport complet » one-shot :
 *  1. interrupteur : rien ne change tant que b2c.enforced = false ;
 *  2. l'épreuve d'appel reste jouable par un auto-inscrit non payeur ;
 *  3. les autres épreuves et le Grimoire sont verrouillés pour lui ;
 *  4. les candidats invités par un pro ne sont JAMAIS bloqués ;
 *  5. un achat payé débloque tout ;
 *  6. le checkout exige l'acceptation des CGV et un produit connu ;
 *  7. le webhook checkout.session.completed marque l'achat payé.
 */

use App\Listeners\RecordB2cPurchaseOnCheckoutCompleted;
use App\Models\CandidatePurchase;
use App\Models\Profile;
use App\Models\Test;
use App\Models\TestInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;

beforeEach(function () {
    Mail::fake(); // TestInvitation::created envoie un email réel sinon
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

function b2cCandidate(): User
{
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();
    return $user;
}

function b2cPublishedTest(string $slug): Test
{
    $test = Test::create([
        'slug'              => $slug,
        'name'              => 'Épreuve ' . $slug,
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);

    $section = \App\Models\TestSection::create([
        'test_id' => $test->id,
        'title'   => 'Section',
        'order'   => 0,
    ]);

    \App\Models\TestQuestion::create([
        'section_id' => $section->id,
        'type'       => 'scale',
        'prompt'     => 'Question',
        'order'      => 0,
        'options'    => ['min' => 1, 'max' => 5],
        'required'   => true,
    ]);

    return $test->fresh('sections.questions');
}

// ─── 1. Interrupteur ──────────────────────────────────────────────────────────

it('ne bloque rien tant que le paywall B2C est désactivé', function () {
    config(['b2c.enforced' => false]);

    $user = b2cCandidate();
    $test = b2cPublishedTest('praximum');

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect(); // vers attempt.show, pas vers /debloquer

    expect(session('info'))->toBeNull();
    $this->assertDatabaseHas('test_attempts', ['user_id' => $user->id, 'test_id' => $test->id]);
});

it('redirige la page de déblocage vers les tests quand le paywall est désactivé', function () {
    config(['b2c.enforced' => false]);

    $this->actingAs(b2cCandidate())
        ->get(route('b2c.unlock'))
        ->assertRedirect(route('tests.index'));
});

// ─── 2 & 3. Auto-inscrit non payeur ──────────────────────────────────────────

it("laisse l'auto-inscrit jouer l'épreuve d'appel gratuite", function () {
    config(['b2c.enforced' => true, 'b2c.free_test_slugs' => ['orientation-express']]);

    $user = b2cCandidate();
    $test = b2cPublishedTest('orientation-express');

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect();

    $this->assertDatabaseHas('test_attempts', ['user_id' => $user->id, 'test_id' => $test->id]);
});

it("verrouille les autres épreuves pour l'auto-inscrit non payeur", function () {
    config(['b2c.enforced' => true]);

    $user = b2cCandidate();
    $test = b2cPublishedTest('praximum');

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect(route('b2c.unlock'));

    $this->assertDatabaseMissing('test_attempts', ['user_id' => $user->id, 'test_id' => $test->id]);
});

it("verrouille le Grimoire pour l'auto-inscrit non payeur", function () {
    config(['b2c.enforced' => true]);

    $this->actingAs(b2cCandidate())
        ->get(route('grimoire.show'))
        ->assertRedirect(route('b2c.unlock'));
});

it('affiche la page de déblocage à un auto-inscrit verrouillé', function () {
    config(['b2c.enforced' => true]);

    $this->actingAs(b2cCandidate())
        ->get(route('b2c.unlock'))
        ->assertOk();
});

// ─── 4. Candidat invité par un pro ───────────────────────────────────────────

it("ne bloque jamais un candidat invité par un professionnel", function () {
    config(['b2c.enforced' => true]);

    $user = b2cCandidate();
    $test = b2cPublishedTest('praximum');

    TestInvitation::create([
        'test_id' => $test->id,
        'email'   => $user->email,
    ]);

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect();

    $this->assertDatabaseHas('test_attempts', ['user_id' => $user->id, 'test_id' => $test->id]);
});

// ─── 5. Achat payé ───────────────────────────────────────────────────────────

it('débloque tout après un achat payé', function () {
    config(['b2c.enforced' => true]);

    $user = b2cCandidate();
    $test = b2cPublishedTest('praximum');

    CandidatePurchase::create([
        'user_id' => $user->id,
        'product' => 'rapport',
        'amount'  => 4900,
        'status'  => 'paid',
        'paid_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect();

    $this->assertDatabaseHas('test_attempts', ['user_id' => $user->id, 'test_id' => $test->id]);
});

it("un achat pending ne débloque rien", function () {
    config(['b2c.enforced' => true]);

    $user = b2cCandidate();
    $test = b2cPublishedTest('praximum');

    CandidatePurchase::create([
        'user_id' => $user->id,
        'product' => 'rapport',
        'amount'  => 4900,
        'status'  => 'pending',
    ]);

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect(route('b2c.unlock'));
});

// ─── 6. Validation du checkout ───────────────────────────────────────────────

it('refuse un checkout sans acceptation des CGV', function () {
    config(['b2c.enforced' => true]);

    $this->actingAs(b2cCandidate())
        ->postJson(route('b2c.checkout'), ['product' => 'rapport'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('cgv');
});

it('refuse un checkout sur un produit inconnu', function () {
    config(['b2c.enforced' => true]);

    $this->actingAs(b2cCandidate())
        ->postJson(route('b2c.checkout'), ['product' => 'produit_pirate', 'cgv' => true])
        ->assertStatus(422)
        ->assertJsonValidationErrors('product');
});

it('refuse un checkout sur un produit non disponible', function () {
    config(['b2c.enforced' => true, 'b2c.products.rapport_debrief.available' => false]);

    $this->actingAs(b2cCandidate())
        ->post(route('b2c.checkout'), ['product' => 'rapport_debrief', 'cgv' => true])
        ->assertRedirect(route('b2c.unlock'));

    expect(CandidatePurchase::count())->toBe(0);
});

// ─── 7. Webhook Stripe ───────────────────────────────────────────────────────

it("marque l'achat payé à la réception du webhook checkout.session.completed", function () {
    $user = b2cCandidate();

    $purchase = CandidatePurchase::create([
        'user_id'           => $user->id,
        'product'           => 'rapport',
        'amount'            => 4900,
        'status'            => 'pending',
        'stripe_session_id' => 'cs_test_123',
    ]);

    (new RecordB2cPurchaseOnCheckoutCompleted)->handle(new WebhookReceived([
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id'             => 'cs_test_123',
            'payment_status' => 'paid',
            'metadata'       => ['b2c_purchase_id' => (string) $purchase->id],
        ]],
    ]));

    expect($purchase->fresh()->status)->toBe('paid')
        ->and($purchase->fresh()->paid_at)->not->toBeNull()
        ->and($user->fresh()->hasPaidB2cUnlock())->toBeTrue();
});

it("ignore un webhook checkout non payé ou sans metadata B2C", function () {
    $user = b2cCandidate();

    $purchase = CandidatePurchase::create([
        'user_id'           => $user->id,
        'product'           => 'rapport',
        'amount'            => 4900,
        'status'            => 'pending',
        'stripe_session_id' => 'cs_test_456',
    ]);

    // Non payé
    (new RecordB2cPurchaseOnCheckoutCompleted)->handle(new WebhookReceived([
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id'             => 'cs_test_456',
            'payment_status' => 'unpaid',
            'metadata'       => ['b2c_purchase_id' => (string) $purchase->id],
        ]],
    ]));

    // Sans metadata (checkout d'un autre produit)
    (new RecordB2cPurchaseOnCheckoutCompleted)->handle(new WebhookReceived([
        'type' => 'checkout.session.completed',
        'data' => ['object' => ['id' => 'cs_autre', 'payment_status' => 'paid', 'metadata' => []]],
    ]));

    expect($purchase->fresh()->status)->toBe('pending');
});
