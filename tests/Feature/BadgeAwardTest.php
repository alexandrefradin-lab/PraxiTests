<?php

/**
 * Non-régression sur l'attribution des badges.
 *
 * Contexte : trois 500 en production (06/07, 15/07, 16/07/2026) sur
 * « Duplicate entry 'X-Y' for key user_badges_user_id_badge_id_unique »,
 * sur trois candidats différents, en pleine gamification.
 *
 * Cause racine : award() créditait des Éclats via awardXp() qui RÉ-ÉVALUAIT les
 * badges ; la passe récursive attribuait les badges suivants avant que la boucle
 * appelante ne les atteigne, d'où la double insertion.
 *
 * Corrigé le 16/07 (commit a1ed0ff) par syncWithoutDetaching + catch de la
 * violation d'unicité + awardXp(evaluateBadges: false). Ces tests verrouillent
 * ce comportement : ils échouent contre le code d'avant le correctif.
 */

use App\Models\Badge;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestSection;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Praxis\Core\Gamification\BadgeEvaluator;
use Praxis\Core\Gamification\GamificationEngine;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
    // BadgeEvaluator cache Badge::all() 300 s sous 'gamification.badges.all' :
    // sans purge, un test verrait le catalogue du test précédent.
    Cache::flush();
});

// ─── Helpers ────────────────────────────────────────────────────────────────

function badgeMake(string $slug, array $criteria, int $xpReward = 0): Badge
{
    return Badge::create([
        'slug'      => $slug,
        'name'      => 'Badge ' . $slug,
        'criteria'  => $criteria,
        'xp_reward' => $xpReward,
    ]);
}

/** Un candidat ayant terminé une Épreuve (satisfait le critère tests_completed). */
function badgeCandidate(): User
{
    $user = User::factory()->create();

    $test = Test::create([
        'slug'              => 'badge-epreuve-' . uniqid(),
        'name'              => 'Épreuve',
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);
    TestSection::create(['test_id' => $test->id, 'title' => 'S', 'order' => 0]);

    TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'completed',
        'started_at'       => now(),
        'completed_at'     => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);

    return $user;
}

function badgeEclats(User $user): int
{
    return app(GamificationEngine::class)->totalEclats($user);
}

// ─── 1. Double attribution directe ──────────────────────────────────────────

it('n attribue pas deux fois le même badge', function () {
    $user  = User::factory()->create();
    $badge = badgeMake('double', ['type' => 'first_test'], 120);

    $evaluator = app(BadgeEvaluator::class);

    $evaluator->award($user, $badge);
    $evaluator->award($user, $badge); // double-clic / retry

    expect($user->badges()->count())->toBe(1)
        // et surtout : les Éclats ne sont crédités qu'UNE fois
        ->and(badgeEclats($user))->toBe(120);
});

// ─── 2. Cascade : le gain d'un badge en débloque un autre ───────────────────

it('attribue en cascade sans doublon quand les Éclats d un badge en débloquent un autre', function () {
    // Ordre d'évaluation = ordre d'insertion. Le premier rapporte assez
    // d'Éclats pour satisfaire le critère du second DANS LA MÊME PASSE :
    // c'est exactement le scénario qui produisait la violation d'unicité.
    $premier = badgeMake('cascade-1', ['type' => 'tests_completed', 'min' => 1], 500);
    $second  = badgeMake('cascade-2', ['type' => 'xp_total', 'min' => 400], 0);

    $user = badgeCandidate();

    app(BadgeEvaluator::class)->evaluate($user, ['type' => 'attempt_completed']);

    $earned = $user->badges()->pluck('badges.id')->all();

    expect($earned)->toContain($premier->id)
        ->and($earned)->toContain($second->id)
        ->and($user->badges()->count())->toBe(2)
        // 500 une seule fois : une ré-évaluation récursive aurait re-crédité
        ->and(badgeEclats($user))->toBe(500);
});

// ─── 3. Ré-évaluation complète ──────────────────────────────────────────────

it('reste idempotent quand toute la passe d évaluation est rejouée', function () {
    badgeMake('rejoue-1', ['type' => 'tests_completed', 'min' => 1], 500);
    badgeMake('rejoue-2', ['type' => 'xp_total', 'min' => 400], 0);

    $user      = badgeCandidate();
    $evaluator = app(BadgeEvaluator::class);

    $evaluator->evaluate($user, ['type' => 'attempt_completed']);
    $evaluator->evaluate($user, ['type' => 'attempt_completed']);
    $evaluator->evaluate($user, ['type' => 'xp_awarded']);

    expect($user->badges()->count())->toBe(2)
        ->and(badgeEclats($user))->toBe(500);
});

// ─── 4. Le crédit d'Éclats d'un badge ne relance pas de passe de badges ─────

it('ne relance pas une passe de badges en créditant les Éclats d un badge', function () {
    // Si award() ré-évaluait les badges, ce badge « xp_total » serait attribué
    // par la récursion — donc AVANT que la boucle appelante ne l'atteigne.
    $porteur = badgeMake('porteur', ['type' => 'first_test'], 1000);
    $cible   = badgeMake('cible', ['type' => 'xp_total', 'min' => 100], 0);

    $user = badgeCandidate();

    app(BadgeEvaluator::class)->award($user, $porteur);

    // award() n'attribue QUE le badge demandé : la cible attend la passe suivante.
    expect($user->badges()->pluck('badges.id')->all())->toBe([$porteur->id])
        ->and($user->badges()->count())->toBe(1);

    app(BadgeEvaluator::class)->evaluate($user, ['type' => 'xp_awarded']);

    expect($user->badges()->count())->toBe(2)
        ->and(badgeEclats($user))->toBe(1000);
});
