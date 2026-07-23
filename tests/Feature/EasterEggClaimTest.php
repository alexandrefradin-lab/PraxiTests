<?php

/**
 * Non-régression sur la route de récompense des easter eggs.
 *
 * Contexte : POST /easter-egg/claim a renvoyé 500 depuis sa mise en ligne.
 * `EasterEggController::class` était écrit en nom court dans routes/web.php,
 * fichier sans namespace ; Laravel 11 n'appliquant plus de préfixe de
 * contrôleur, l'expression se résolvait en la chaîne "EasterEggController",
 * introuvable au dispatch. Le composant Vue avalait l'erreur et affichait une
 * réussite : aucun Éclat, aucun badge, aucune alerte — pendant des mois.
 *
 * Corrigé le 22/07/2026 en qualifiant pleinement le contrôleur. Ces tests
 * échouent contre le code d'avant : le premier renvoyait 500 au lieu de 200.
 */

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
    Cache::flush();

    // Les badges des easter eggs viennent de BadgeSeeder en prod ; ici on ne
    // crée que celui utilisé par le slug testé.
    Badge::create([
        'slug'      => 'eveille',
        'name'      => 'Éveillé',
        'criteria'  => ['type' => 'easter_egg'],
        'xp_reward' => 0,
    ]);
});

function eggUser(): User
{
    return User::factory()->create(['email_verified_at' => now()]);
}

/** Une tentative en cours + l'id d'une question, pour tester les révisions. */
function eggAttempt(User $user): array
{
    $test = \App\Models\Test::create([
        'slug'              => 'egg-epreuve-' . uniqid(),
        'name'              => 'Épreuve',
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);
    $section = \App\Models\TestSection::create(['test_id' => $test->id, 'title' => 'S', 'order' => 0]);
    $question = \App\Models\TestQuestion::create([
        'section_id' => $section->id,
        'order'      => 0,
        'type'       => 'scale',
        'prompt'     => 'Une question',
        'options'    => ['min' => 1, 'max' => 5],
        'required'   => true,
    ]);
    $attempt = \App\Models\TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'in_progress',
        'started_at'       => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);

    return [$attempt, $question->id];
}

it('récompense un secret découvert et le trace en base', function () {
    $user = eggUser();

    $res = $this->actingAs($user)->postJson(route('easter-egg.claim'), ['slug' => 'konami']);

    $res->assertOk()
        ->assertJson(['success' => true, 'eclats' => 42]);

    $this->assertDatabaseHas('user_easter_eggs', [
        'user_id' => $user->id,
        'slug'    => 'konami',
    ]);

    expect($user->fresh()->badges()->where('slug', 'eveille')->exists())->toBeTrue();
});

it('refuse un second claim du même secret', function () {
    $user = eggUser();

    $this->actingAs($user)->postJson(route('easter-egg.claim'), ['slug' => 'konami'])->assertOk();

    $this->actingAs($user)
        ->postJson(route('easter-egg.claim'), ['slug' => 'konami'])
        ->assertOk()
        ->assertJson(['already_claimed' => true]);

    // Une seule ligne, donc un seul crédit.
    expect(\DB::table('user_easter_eggs')->where('user_id', $user->id)->count())->toBe(1);
});

it('rejette un slug absent du registre serveur', function () {
    $user = eggUser();

    $this->actingAs($user)
        ->postJson(route('easter-egg.claim'), ['slug' => 'slug_qui_nexiste_pas'])
        ->assertStatus(422);

    expect(\DB::table('user_easter_eggs')->where('user_id', $user->id)->count())->toBe(0);
});

it('exige une authentification', function () {
    $this->postJson(route('easter-egg.claim'), ['slug' => 'konami'])
        ->assertStatus(401);
});

it('attribue Constellation quand tous les autres badges sont obtenus', function () {
    $user = eggUser();

    // Catalogue minimal : deux badges ordinaires + le meta-badge.
    Badge::create(['slug' => 'a', 'name' => 'A', 'criteria' => ['type' => 'easter_egg'], 'xp_reward' => 0]);
    Badge::create(['slug' => 'b', 'name' => 'B', 'criteria' => ['type' => 'easter_egg'], 'xp_reward' => 0]);
    $meta = Badge::create([
        'slug' => 'constellation', 'name' => 'Constellation',
        'criteria' => ['type' => 'all_badges'], 'xp_reward' => 0,
    ]);

    $evaluator = app(\Praxis\Core\Gamification\BadgeEvaluator::class);

    // Il manque 'eveille', 'a' et 'b' : le meta ne doit PAS tomber.
    $evaluator->evaluate($user, ['type' => 'test']);
    expect($user->fresh()->badges()->where('slug', 'constellation')->exists())->toBeFalse();

    // On donne tout le reste : le meta doit alors tomber, sans se compter lui-meme.
    foreach (['eveille', 'a', 'b'] as $slug) {
        $evaluator->award($user, Badge::where('slug', $slug)->first());
    }
    \Illuminate\Support\Facades\Cache::flush();
    $evaluator->evaluate($user->fresh(), ['type' => 'test']);

    expect($user->fresh()->badges()->where('slug', 'constellation')->exists())->toBeTrue();
});

it('ne compte que les vrais changements de reponse', function () {
    $user = eggUser();
    $engine = app(\Praxis\Core\TestEngine\TestEngine::class);

    [$attempt, $questionId] = eggAttempt($user);

    $engine->recordAnswer($attempt, $questionId, 3);
    $engine->recordAnswer($attempt, $questionId, 3); // autosave : valeur identique
    $engine->recordAnswer($attempt, $questionId, 3);

    expect($attempt->answers()->where('question_id', $questionId)->value('revisions'))->toBe(0);

    $engine->recordAnswer($attempt, $questionId, 4);
    $engine->recordAnswer($attempt, $questionId, 2);

    expect($attempt->answers()->where('question_id', $questionId)->value('revisions'))->toBe(2);
});
