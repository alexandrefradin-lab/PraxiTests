<?php

/**
 * Déblocage CHOISI des mini-apps de La Salle du Trésor.
 *
 * Règles couvertes :
 *  1. Porte d'entrée — rien ne s'ouvre tant que TOUTES les Épreuves ne sont pas passées.
 *  2. Choix — le candidat décide quelle mini-app ouvrir, dans l'ordre qu'il veut.
 *  3. Dépense — les Éclats sont débités du portefeuille (le cumul, lui, ne bouge pas).
 *  4. Idempotence — un double-clic n'ouvre ni ne facture deux fois.
 *  5. Garde d'accès — une mini-app non ouverte reste inaccessible, y compris par URL directe.
 */

use App\Models\GamificationProgress;
use App\Models\MiniAppUnlock;
use App\Models\Plugin;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestSection;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Praxis\Core\Gamification\MiniAppUnlockService;
use Praxis\Core\Journey\JourneyRegistry;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
    Cache::flush(); // reward_catalog_v2 + eclats.{id}
});

// ─── Helpers ────────────────────────────────────────────────────────────────

/** Une Épreuve publiée : elle compte dans la porte d'entrée. */
function mauTest(string $slug = null): Test
{
    $test = Test::create([
        'slug'              => $slug ?? 'epreuve-' . uniqid(),
        'name'              => 'Épreuve',
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);
    TestSection::create(['test_id' => $test->id, 'title' => 'S', 'order' => 0]);

    return $test;
}

/** Une mini-app « parcours 60 jours » vendue $cost Éclats. */
function mauMiniApp(string $slug, int $cost): void
{
    Cache::flush();

    Plugin::create([
        'slug'             => $slug,
        'name'             => 'Mini ' . $slug,
        'version'          => '1.0.0',
        'type'             => 'journey',
        'service_provider' => 'Test\\Provider',
        'enabled'          => true,
        'manifest'         => [
            'name'   => 'Mini ' . $slug,
            'test'   => ['name' => 'Mini ' . $slug],
            'reward' => ['threshold_eclats' => $cost],
        ],
    ]);

    JourneyRegistry::register($slug, [
        'title'    => 'Mini ' . $slug,
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

function mauGiveEclats(User $user, int $amount): void
{
    GamificationProgress::create([
        'user_id'  => $user->id,
        'test_id'  => null,
        'xp_total' => $amount,
        'level'    => 1,
    ]);
}

function mauCompleteEpreuve(User $user, Test $test): void
{
    TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'completed',
        'started_at'       => now(),
        'completed_at'     => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);
}

/** Candidat ayant tout terminé et disposant de $eclats. */
function mauReadyCandidate(int $eclats, int $epreuves = 2): User
{
    $user = User::factory()->create();

    for ($i = 0; $i < $epreuves; $i++) {
        mauCompleteEpreuve($user, mauTest());
    }

    mauGiveEclats($user, $eclats);

    return $user;
}

// ─── 1. Porte d'entrée : toutes les Épreuves ────────────────────────────────

it('refuse d ouvrir une mini-app tant que toutes les Épreuves ne sont pas passées', function () {
    mauMiniApp('minigate', 100);

    $user = User::factory()->create();
    mauCompleteEpreuve($user, mauTest()); // 1 Épreuve sur 2
    mauTest();                          // celle-ci reste à faire
    mauGiveEclats($user, 10_000);             // les Éclats ne suffisent pas : la porte prime

    $this->actingAs($user)
        ->post(route('treasure.unlock', 'minigate'))
        ->assertRedirect();

    expect(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(0);
});

it('ouvre la porte quand la dernière Épreuve est passée', function () {
    mauMiniApp('minigateopen', 100);

    $user = mauReadyCandidate(500);

    $this->actingAs($user)
        ->post(route('treasure.unlock', 'minigateopen'));

    expect(MiniAppUnlock::where('user_id', $user->id)->where('plugin_slug', 'minigateopen')->exists())
        ->toBeTrue();
});

it('garde la porte fermée si aucune Épreuve n est publiée', function () {
    mauMiniApp('minivide', 100);

    $user = User::factory()->create();
    mauGiveEclats($user, 10_000);

    $this->actingAs($user)->post(route('treasure.unlock', 'minivide'));

    expect(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(0);
});

// ─── 2. Le candidat choisit ─────────────────────────────────────────────────

it('laisse le candidat choisir une mini-app chère avant une moins chère', function () {
    mauMiniApp('minicheap', 100);
    mauMiniApp('miniexpensive', 800);

    $user = mauReadyCandidate(1000);

    $this->actingAs($user)->post(route('treasure.unlock', 'miniexpensive'));

    $slugs = MiniAppUnlock::slugsFor($user->id);

    expect($slugs)->toContain('miniexpensive')
        ->and($slugs)->not->toContain('minicheap');
});

// ─── 3. Dépense : le portefeuille baisse, le cumul non ──────────────────────

it('débite le portefeuille sans toucher au cumul d Éclats', function () {
    mauMiniApp('minispend', 300);

    $user    = mauReadyCandidate(1000);
    $service = app(MiniAppUnlockService::class);

    $service->unlock($user, 'minispend');

    expect($service->availableEclats($user))->toBe(700)
        ->and($service->spentEclats($user))->toBe(300)
        // le cumul pilote le niveau : il ne doit jamais régresser
        ->and(app(\Praxis\Core\Gamification\GamificationEngine::class)->totalEclats($user))->toBe(1000);
});

it('refuse une mini-app hors budget', function () {
    mauMiniApp('minitoorich', 5000);

    $user = mauReadyCandidate(1000);

    $this->actingAs($user)->post(route('treasure.unlock', 'minitoorich'));

    expect(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(0);
});

it('empêche de tout ouvrir avec le budget d une seule mini-app', function () {
    mauMiniApp('minifirst', 600);
    mauMiniApp('minisecond', 600);

    $user = mauReadyCandidate(1000); // de quoi n'en offrir qu'une

    $this->actingAs($user)->post(route('treasure.unlock', 'minifirst'));
    $this->actingAs($user)->post(route('treasure.unlock', 'minisecond'));

    expect(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(1)
        ->and(app(MiniAppUnlockService::class)->availableEclats($user))->toBe(400);
});

// ─── 4. Idempotence (double-clic) ───────────────────────────────────────────

it('ne facture pas deux fois une mini-app déjà ouverte', function () {
    mauMiniApp('minidouble', 200);

    $user    = mauReadyCandidate(1000);
    $service = app(MiniAppUnlockService::class);

    $service->unlock($user, 'minidouble');
    $service->unlock($user, 'minidouble'); // double-clic

    expect(MiniAppUnlock::where('user_id', $user->id)->where('plugin_slug', 'minidouble')->count())->toBe(1)
        ->and($service->spentEclats($user))->toBe(200);
});

// ─── 5. Garde d'accès (contournement par URL directe) ───────────────────────

it('bloque une mini-app non ouverte même avec assez d Éclats', function () {
    mauMiniApp('miniguard', 100);

    // Cumul très supérieur au coût : sous l'ancien système, cela suffisait.
    $user = mauReadyCandidate(9999);

    $this->actingAs($user)
        ->get(route('journey.index', ['plugin' => 'miniguard']))
        ->assertRedirect(route('treasure.index'));

    $this->actingAs($user)
        ->get(route('journey.show', ['plugin' => 'miniguard', 'day' => 1]))
        ->assertRedirect(route('treasure.index'));

    $this->actingAs($user)
        ->post(route('journey.complete.day', ['plugin' => 'miniguard', 'day' => 1]))
        ->assertRedirect(route('treasure.index'));
});

it('donne accès à la mini-app une fois ouverte', function () {
    mauMiniApp('miniopened', 100);

    $user = mauReadyCandidate(500);
    app(MiniAppUnlockService::class)->unlock($user, 'miniopened');

    $this->actingAs($user)
        ->get(route('journey.index', ['plugin' => 'miniopened']))
        ->assertOk();
});

// ─── 6. Payload de la Salle du Trésor ───────────────────────────────────────

it('expose le portefeuille et l état de la porte à la Salle du Trésor', function () {
    mauMiniApp('minipayload', 400);

    $user = mauReadyCandidate(1000);
    app(MiniAppUnlockService::class)->unlock($user, 'minipayload');

    $treasure = app(\Praxis\Core\Gamification\RewardCatalog::class)->forUser($user->fresh());

    expect($treasure['total'])->toBe(1000)
        ->and($treasure['spent'])->toBe(400)
        ->and($treasure['available'])->toBe(600)
        ->and($treasure['gate_open'])->toBeTrue()
        ->and($treasure['unlocked_count'])->toBe(1);

    $item = collect($treasure['items'])->firstWhere('plugin_slug', 'minipayload');

    expect($item['unlocked'])->toBeTrue()
        ->and($item['cost'])->toBe(400)
        ->and($item['url'])->not->toBeNull();
});
