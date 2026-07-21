<?php

/**
 * Déblocage CHOISI des mini-apps de La Salle du Trésor.
 *
 * Règles couvertes :
 *  1. Au fil de l'eau — les Éclats se gagnent en passant les Épreuves, et une
 *     mini-app s'ouvre dès que le solde le permet. Aucune condition d'avancement.
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

    // Ces tests décrivent le régime « choix ». Il est livré DÉSACTIVÉ
    // (PRAXIQUEST_TREASURE_CHOICE_ENABLED) : on l'active explicitement ici.
    // Le régime historique a ses propres tests en fin de fichier.
    config()->set('praxiquest.treasure.choice_enabled', true);
});

// ─── Helpers ────────────────────────────────────────────────────────────────

/** Une Épreuve publiée. */
function mauTest(?string $slug = null): Test
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

/**
 * Crédite des Éclats. Cumulable : gamification_progress porte un unique
 * (user_id, test_id), un second create() sur la même ligne violerait la contrainte.
 */
function mauGiveEclats(User $user, int $amount): void
{
    $row = GamificationProgress::firstOrCreate(
        ['user_id' => $user->id, 'test_id' => null],
        ['xp_total' => 0, 'level' => 1],
    );

    $row->increment('xp_total', $amount);
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

/** Candidat en cours de parcours, disposant de $eclats. */
function mauReadyCandidate(int $eclats, int $epreuves = 2): User
{
    $user = User::factory()->create();

    for ($i = 0; $i < $epreuves; $i++) {
        mauCompleteEpreuve($user, mauTest());
    }

    mauGiveEclats($user, $eclats);

    return $user;
}

// ─── 1. Au fil de l'eau : aucune condition d'avancement ─────────────────────

it('ouvre une mini-app des que le solde suffit, sans exiger la fin des Épreuves', function () {
    mauMiniApp('minitot', 100);

    // Aucune Épreuve terminée : seuls comptent les Éclats déjà gagnés.
    $user = User::factory()->create();
    mauGiveEclats($user, 500);

    $this->actingAs($user)->post(route('treasure.unlock', 'minitot'));

    expect(MiniAppUnlock::where('user_id', $user->id)->where('plugin_slug', 'minitot')->exists())
        ->toBeTrue();
});

it('ouvre une mini-app en cours de parcours, une Épreuve sur deux terminée', function () {
    mauMiniApp('minimilieu', 300);

    $user = User::factory()->create();
    mauCompleteEpreuve($user, mauTest()); // 1 Épreuve sur 2
    mauTest();                            // celle-ci reste à passer
    mauGiveEclats($user, 400);

    $this->actingAs($user)->post(route('treasure.unlock', 'minimilieu'));

    expect(MiniAppUnlock::slugsFor($user->id))->toContain('minimilieu')
        ->and(app(MiniAppUnlockService::class)->availableEclats($user))->toBe(100);
});

it('ouvre les mini-apps petit a petit, au rythme des Éclats gagnes', function () {
    mauMiniApp('minipalier1', 200);
    mauMiniApp('minipalier2', 500);

    $user = User::factory()->create();
    mauGiveEclats($user, 300); // de quoi n'ouvrir que la première

    $this->actingAs($user)->post(route('treasure.unlock', 'minipalier1'));
    $this->actingAs($user)->post(route('treasure.unlock', 'minipalier2'));

    expect(MiniAppUnlock::slugsFor($user->id))->toBe(['minipalier1']);

    // Le candidat continue ses Épreuves et gagne de quoi s'offrir la seconde.
    mauGiveEclats($user, 500);
    $this->actingAs($user)->post(route('treasure.unlock', 'minipalier2'));

    expect(MiniAppUnlock::slugsFor($user->id))->toContain('minipalier2')
        ->and(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(2);
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

it('refuse un slug de mini-app inconnu', function () {
    mauMiniApp('minireal', 100);

    $user = mauReadyCandidate(1000);

    $this->actingAs($user)->post(route('treasure.unlock', 'nexistepas'));

    expect(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(0);
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
        ->and($treasure['unlocked_count'])->toBe(1);

    $item = collect($treasure['items'])->firstWhere('plugin_slug', 'minipayload');

    expect($item['unlocked'])->toBeTrue()
        ->and($item['cost'])->toBe(400)
        ->and($item['url'])->not->toBeNull();
});

// ─── 7. Régime HISTORIQUE (flag off) — comportement livré par défaut ─────────
//
// La feature est livrée désactivée : tant que PRAXIQUEST_TREASURE_CHOICE_ENABLED
// reste à false, plus rien ne doit changer pour les candidats. C'est ce qui rend
// ce lot déployable sans relecture — ces tests sont la garantie.

it('debloque automatiquement au palier quand le choix est desactive', function () {
    config()->set('praxiquest.treasure.choice_enabled', false);
    mauMiniApp('legacyauto', 500);

    $user = User::factory()->create();
    mauGiveEclats($user, 600); // palier franchi, aucune Épreuve terminée

    // Aucun achat : l'accès est immédiat au palier, comme avant.
    $this->actingAs($user)
        ->get(route('journey.index', ['plugin' => 'legacyauto']))
        ->assertOk();

    expect(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(0);
});

it('garde la mini-app scellee sous le palier quand le choix est desactive', function () {
    config()->set('praxiquest.treasure.choice_enabled', false);
    mauMiniApp('legacylocked', 500);

    $user = User::factory()->create();
    mauGiveEclats($user, 100);

    $this->actingAs($user)
        ->get(route('journey.index', ['plugin' => 'legacylocked']))
        ->assertRedirect(route('treasure.index'));
});

it('refuse tout achat quand le choix est desactive', function () {
    config()->set('praxiquest.treasure.choice_enabled', false);
    mauMiniApp('legacybuy', 100);

    $user = mauReadyCandidate(5000); // tout terminé, largement les moyens

    $this->actingAs($user)->post(route('treasure.unlock', 'legacybuy'));

    expect(MiniAppUnlock::where('user_id', $user->id)->count())->toBe(0);
});

it('expose un payload sans porte ni depense quand le choix est desactive', function () {
    config()->set('praxiquest.treasure.choice_enabled', false);
    mauMiniApp('legacypayload', 400);

    $user = User::factory()->create();
    mauGiveEclats($user, 1000); // aucune Épreuve terminée

    $treasure = app(\Praxis\Core\Gamification\RewardCatalog::class)->forUser($user);

    expect($treasure['choice_enabled'])->toBeFalse()
        ->and($treasure['spent'])->toBe(0)
        ->and($treasure['available'])->toBe(1000)
        ->and($treasure['unlocked_count'])->toBe(1); // débloqué par le seul palier

    $item = collect($treasure['items'])->firstWhere('plugin_slug', 'legacypayload');

    expect($item['unlocked'])->toBeTrue()
        ->and($item['affordable'])->toBeFalse()      // aucun bouton d'achat
        ->and($item['url'])->not->toBeNull();
});
