<?php

/**
 * Tests anti-régression du Lot 2 (audit 2026-07-16).
 *
 * 1. Gating Éclats : une mini-app « parcours 60 jours » verrouillée redirige
 *    vers la Salle du Trésor (SEC-M1 — contournement par URL directe colmaté).
 * 2. Le nom de route du « tip du jour » reste stable (B1 — le front appelait
 *    un nom inexistant, ce qui cassait tips + série).
 * 3. Inscription via lien d'invitation : email auto-vérifié + invitation liée
 *    au compte (rattachement robuste, hors session).
 * 4. Vue résultat pro : ouverte au professionnel invitant uniquement si le
 *    candidat a consenti (SEC-M12), cloisonnée à son compte professionnel.
 */

use App\Models\GamificationProgress;
use App\Models\Plugin;
use App\Models\ProfessionalAccount;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestInvitation;
use App\Models\TestResult;
use App\Models\TestSection;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Praxis\Core\Journey\JourneyRegistry;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
});

// ─── Helpers ────────────────────────────────────────────────────────────────

function lot2Test(): Test
{
    $test = Test::create([
        'slug'              => 'lot2-test-' . uniqid(),
        'name'              => 'Lot2 Test',
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);
    TestSection::create(['test_id' => $test->id, 'title' => 'S', 'order' => 0]);

    return $test;
}

/** Enregistre une mini-app « parcours » (Plugin + JourneyRegistry) avec un palier. */
function lot2RegisterJourney(string $slug, int $threshold): void
{
    Cache::flush(); // purge reward_catalog_v2 pour que le nouveau plugin soit vu

    Plugin::create([
        'slug'             => $slug,
        'name'             => 'Journey ' . $slug,
        'version'          => '1.0.0',
        'type'             => 'journey',
        'service_provider' => 'Test\\Provider',
        'enabled'          => true,
        'manifest'         => [
            'name'   => 'Journey ' . $slug,
            'test'   => ['name' => 'Journey ' . $slug],
            'reward' => ['threshold_eclats' => $threshold],
        ],
    ]);

    JourneyRegistry::register($slug, [
        'title'    => 'Journey ' . $slug,
        'subtitle' => '',
        'color'    => '#000000',
        'days'     => [[
            'day'             => 1,
            'theme'           => 'Intro',
            'title'           => 'Jour 1',
            'summary'         => 'Résumé',
            'body'            => 'Corps',
            'micro_challenge' => 'Défi',
            'duration_min'    => 5,
            'icon'            => 'sparkles',
        ]],
    ]);
}

/** Crée un compte professionnel valide (owner_user_id est NOT NULL en schéma). */
function lot2Account(string $name): ProfessionalAccount
{
    return ProfessionalAccount::create([
        'owner_user_id' => User::factory()->create()->id,
        'company_name'  => $name,
    ]);
}

function lot2Professional(ProfessionalAccount $account): User
{
    Role::firstOrCreate(['name' => 'professional', 'guard_name' => 'web']);
    $pro = User::factory()->create();
    $pro->assignRole('professional');
    $pro->professionalAccounts()->attach($account->id, ['role' => 'owner']);

    return $pro;
}

// ─── 1. Gating Éclats sur les parcours (SEC-M1) ──────────────────────────────

it('redirects a journey mini-app to the treasure room when it has not been unlocked', function () {
    lot2RegisterJourney('lot2journeylocked', 500);
    $user = User::factory()->create(); // 0 Éclat

    // index, show ET complete doivent tous rediriger (le trou d'origine était sur show/complete)
    $this->actingAs($user)
        ->get(route('journey.index', ['plugin' => 'lot2journeylocked']))
        ->assertRedirect(route('treasure.index'));

    $this->actingAs($user)
        ->get(route('journey.show', ['plugin' => 'lot2journeylocked', 'day' => 1]))
        ->assertRedirect(route('treasure.index'));

    $this->actingAs($user)
        ->post(route('journey.complete.day', ['plugin' => 'lot2journeylocked', 'day' => 1]))
        ->assertRedirect(route('treasure.index'));
});

it('allows a journey mini-app once it has been unlocked in the treasure room', function () {
    // Depuis que les Éclats se dépensent, atteindre le palier NE suffit PLUS :
    // la mini-app doit avoir été explicitement choisie et payée (mini_app_unlocks).
    // Cf. tests/Feature/MiniAppUnlockTest.php pour le parcours de déblocage complet.
    lot2RegisterJourney('lot2journeyopen', 500);
    $user = User::factory()->create();
    GamificationProgress::create([
        'user_id'  => $user->id,
        'test_id'  => null,
        'xp_total' => 600,
        'level'    => 1,
    ]);

    \App\Models\MiniAppUnlock::create([
        'user_id'     => $user->id,
        'plugin_slug' => 'lot2journeyopen',
        'cost_eclats' => 500,
        'unlocked_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('journey.index', ['plugin' => 'lot2journeyopen']))
        ->assertOk();
});

// ─── 2. Nom de route du tip du jour (B1) ─────────────────────────────────────

it('keeps the daily tip apply route name stable', function () {
    // Le composant DailyTipCard appelait 'dailytip.apply' (inexistant) → Ziggy
    // jetait au clic. La bonne route est 'daily.tip.apply'.
    $url = route('daily.tip.apply', ['plugin' => 'praxizen']);

    expect($url)->toContain('/apps/praxizen/tip/apply');
});

// ─── 3. Inscription via invitation : auto-vérif email + rattachement ─────────

it('links the invitation and verifies the email when registering via an invitation token', function () {
    $account    = lot2Account('Cabinet');
    $test       = lot2Test();
    $invitation = TestInvitation::create([
        'test_id'                 => $test->id,
        'professional_account_id' => $account->id,
        'email'                   => 'cand@example.com',
        'status'                  => 'sent',
    ]);

    // Atterrissage sur le lien → stocke le token en session et redirige vers register
    $this->get(route('invitation.land', ['token' => $invitation->token]))
        ->assertRedirect();

    // Inscription avec l'email de l'invitation + consentement de partage
    $this->post('/register', [
        'name'                  => 'Candidat',
        'email'                 => 'cand@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
        'terms'                 => true,
        'consent_share'         => true,
        'quest_title'           => 'explorateur',
    ])
        ->assertRedirect(route('onboarding.show'))
        // Rattachement porté par la session (repris par AttemptController au start)
        ->assertSessionHas('pending_invitation_id', $invitation->id);

    $user = User::where('email', 'cand@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasVerifiedEmail())->toBeTrue();

    $invitation->refresh();
    expect($invitation->status)->toBe('started');
    expect($invitation->consent_share_professional)->toBeTrue();
});

// ─── 4. Vue résultat pro sous consentement (SEC-M12) ─────────────────────────

/** Fabrique candidat + tentative terminée + résultat, liés à une invitation. */
function lot2CompletedAttempt(ProfessionalAccount $account, bool $consent): TestAttempt
{
    $test       = lot2Test();
    $candidate  = User::factory()->create();
    $invitation = TestInvitation::create([
        'test_id'                     => $test->id,
        'professional_account_id'     => $account->id,
        'email'                       => $candidate->email,
        'status'                      => 'completed',
        'consent_share_professional'  => $consent,
    ]);
    $attempt = TestAttempt::create([
        'user_id'       => $candidate->id,
        'test_id'       => $test->id,
        'invitation_id' => $invitation->id,
        'status'        => 'completed',
        'started_at'    => now()->subHour(),
        'completed_at'  => now(),
        'progress'      => [],
    ]);
    TestResult::create([
        'attempt_id'   => $attempt->id,
        'ai_synthesis' => 'Synthèse de test.',
        'scoring'      => ['dimensions' => ['role' => 60]],
    ]);

    return $attempt;
}

it('lets the inviting professional view a consented candidate result', function () {
    $account = lot2Account('Cab A');
    $pro     = lot2Professional($account);
    $attempt = lot2CompletedAttempt($account, consent: true);

    $this->actingAs($pro)
        ->get(route('results.show', $attempt))
        ->assertOk();
});

it('forbids the professional when the candidate did not consent', function () {
    $account = lot2Account('Cab B');
    $pro     = lot2Professional($account);
    $attempt = lot2CompletedAttempt($account, consent: false);

    $this->actingAs($pro)
        ->get(route('results.show', $attempt))
        ->assertForbidden();
});

it('forbids a professional from another account even with consent', function () {
    $accountOwner = lot2Account('Cab C');
    $accountOther = lot2Account('Cab D');
    $proOther     = lot2Professional($accountOther);
    $attempt      = lot2CompletedAttempt($accountOwner, consent: true);

    $this->actingAs($proOther)
        ->get(route('results.show', $attempt))
        ->assertForbidden();
});
