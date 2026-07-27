<?php

use App\Models\Profile;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Praxis\Core\TestEngine\NormInterpreter;

afterEach(function () {
    NormInterpreter::clearCandidateGroup();
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

/** Insère une norme dans test_norms (origin explicite). */
function insertNorm(string $slug, string $dim, float $mean, float $sd, int $n, string $group = 'all', string $origin = 'reference'): void
{
    DB::table('test_norms')->insert([
        'test_slug'   => $slug,
        'dimension'   => $dim,
        'group_key'   => $group,
        'origin'      => $origin,
        'mean'        => $mean,
        'std_dev'     => $sd,
        'n_responses' => $n,
        'source'      => 'test fixture',
        'computed_at' => $origin === 'platform' ? now() : null,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
}

/** Slug unique par test — le cache statique de NormInterpreter survit entre les tests Pest. */
function normSlug(): string
{
    return 'norm-test-' . uniqid();
}

// ─── Chaîne de résolution enrich() ───────────────────────────────────────────

it('uses the reference norm when no platform norm exists', function () {
    $slug = normSlug();
    insertNorm($slug, 'dim', 50.0, 10.0, 1000, 'all', 'reference');

    $r = NormInterpreter::enrich($slug, 'dim', 60.0);

    expect($r['percentile'])->toBe(84);
    expect($r['norm_origin'])->toBe('reference');
    expect($r['norm_group'])->toBe('all');
});

it('prefers the platform norm once it reaches the usage threshold', function () {
    $slug = normSlug();
    insertNorm($slug, 'dim', 50.0, 10.0, 1000, 'all', 'reference');
    insertNorm($slug, 'dim', 60.0, 10.0, NormInterpreter::MIN_USE, 'all', 'platform');

    $r = NormInterpreter::enrich($slug, 'dim', 60.0);

    // 60 est la moyenne plateforme → 50ème percentile.
    expect($r['percentile'])->toBe(50);
    expect($r['norm_origin'])->toBe('platform');
});

it('blends reference and platform norms in the transition zone', function () {
    $slug = normSlug();
    insertNorm($slug, 'dim', 40.0, 10.0, 2400, 'all', 'reference');
    insertNorm($slug, 'dim', 60.0, 10.0, 100, 'all', 'platform'); // 50 ≤ n < 200

    $r = NormInterpreter::enrich($slug, 'dim', 50.0);

    expect($r['norm_origin'])->toBe('blend');
    // Référence plafonnée à 200 : moyenne = (200×40 + 100×60) / 300 ≈ 46.67
    // z = (50 − 46.67) / 10 ≈ 0.33 → ~63ème percentile.
    expect($r['percentile'])->toBeGreaterThan(58)->toBeLessThan(68);
});

it('uses the age-group platform norm for the candidate when large enough', function () {
    $slug = normSlug();
    insertNorm($slug, 'dim', 50.0, 10.0, 1000, 'all', 'reference');
    insertNorm($slug, 'dim', 40.0, 10.0, 250, 'age:55plus', 'platform');

    NormInterpreter::setCandidateGroup('age:55plus');
    $r = NormInterpreter::enrich($slug, 'dim', 40.0);

    // 40 = moyenne du groupe 55+ → 50ème percentile au lieu de 16 avec la norme globale.
    expect($r['percentile'])->toBe(50);
    expect($r['norm_origin'])->toBe('platform');
    expect($r['norm_group'])->toBe('age:55plus');
});

it('falls back to the reference norm when the age-group sample is too small', function () {
    $slug = normSlug();
    insertNorm($slug, 'dim', 50.0, 10.0, 1000, 'all', 'reference');
    insertNorm($slug, 'dim', 40.0, 10.0, 60, 'age:55plus', 'platform'); // < MIN_USE

    NormInterpreter::setCandidateGroup('age:55plus');
    $r = NormInterpreter::enrich($slug, 'dim', 50.0);

    expect($r['norm_origin'])->toBe('reference');
    expect($r['norm_group'])->toBe('all');
});

it('ignores the candidate group after clearCandidateGroup', function () {
    $slug = normSlug();
    insertNorm($slug, 'dim', 50.0, 10.0, 1000, 'all', 'reference');
    insertNorm($slug, 'dim', 40.0, 10.0, 250, 'age:55plus', 'platform');

    NormInterpreter::setCandidateGroup('age:55plus');
    NormInterpreter::clearCandidateGroup();
    $r = NormInterpreter::enrich($slug, 'dim', 50.0);

    expect($r['norm_origin'])->toBe('reference');
});

it('returns a raw-score fallback when no norm exists at all', function () {
    $r = NormInterpreter::enrich(normSlug(), 'dim', 42.0);

    expect($r['score'])->toBe(42.0);
    expect($r['percentile'])->toBeNull();
    expect($r['norm_origin'])->toBeNull();
});

// ─── Recalcul automatique depuis les passations ──────────────────────────────

/**
 * Crée un test + N passations complètes dont le scoring contient des
 * norm_scores, avec la tranche d'âge donnée sur le profil du candidat.
 */
function makeScoredAttempts(Test $test, int $count, ?string $ageBand, float $score): void
{
    for ($i = 0; $i < $count; $i++) {
        $user = User::factory()->create();
        Profile::factory()->for($user)->create(['age_band' => $ageBand]);

        $attempt = TestAttempt::create([
            'user_id'      => $user->id,
            'test_id'      => $test->id,
            'status'       => 'completed',
            'started_at'   => now(),
            'completed_at' => now(),
            'progress'     => [],
        ]);

        TestResult::create([
            'attempt_id' => $attempt->id,
            'scoring'    => [
                'norm_scores' => [
                    'logique' => ['score' => $score, 'percentile' => 50],
                ],
            ],
        ]);
    }
}

it('recomputes platform norms globally and per age band without touching reference norms', function () {
    $engineKey = 'engine-' . uniqid();
    $test = Test::create([
        'slug'           => 'test-' . uniqid(),
        'name'           => 'Test recompute',
        'type'           => 'questionnaire',
        'scoring_engine' => $engineKey,
        'published'      => true,
    ]);

    insertNorm($engineKey, 'logique', 58.0, 20.0, 0, 'all', 'reference');

    makeScoredAttempts($test, 3, '35-44', 70.0);
    makeScoredAttempts($test, 2, null, 40.0);

    $written = NormInterpreter::recomputeForTest($test, 3);

    // 'all' (n=5) et 'age:35-44' (n=3) atteignent le seuil ; pas de groupe pour les profils sans tranche.
    expect($written)->toBe(2);

    $all = DB::table('test_norms')->where(['test_slug' => $engineKey, 'dimension' => 'logique', 'group_key' => 'all', 'origin' => 'platform'])->first();
    expect($all)->not->toBeNull();
    expect((int) $all->n_responses)->toBe(5);
    expect((float) $all->mean)->toBe(58.0); // (3×70 + 2×40) / 5

    $band = DB::table('test_norms')->where(['test_slug' => $engineKey, 'dimension' => 'logique', 'group_key' => 'age:35-44', 'origin' => 'platform'])->first();
    expect($band)->not->toBeNull();
    expect((int) $band->n_responses)->toBe(3);
    expect((float) $band->mean)->toBe(70.0);

    // La norme de référence n'a pas bougé.
    $ref = DB::table('test_norms')->where(['test_slug' => $engineKey, 'dimension' => 'logique', 'group_key' => 'all', 'origin' => 'reference'])->first();
    expect((float) $ref->mean)->toBe(58.0);
    expect((float) $ref->std_dev)->toBe(20.0);
    expect($ref->source)->toBe('test fixture');
});

it('does not write platform norms below the minimum sample', function () {
    $engineKey = 'engine-' . uniqid();
    $test = Test::create([
        'slug'           => 'test-' . uniqid(),
        'name'           => 'Test petit échantillon',
        'type'           => 'questionnaire',
        'scoring_engine' => $engineKey,
        'published'      => true,
    ]);

    makeScoredAttempts($test, 2, null, 50.0);

    expect(NormInterpreter::recomputeForTest($test, 3))->toBe(0);
    expect(DB::table('test_norms')->where('test_slug', $engineKey)->where('origin', 'platform')->count())->toBe(0);
});

it('recomputeAll covers every test with results', function () {
    $engineKey = 'engine-' . uniqid();
    $test = Test::create([
        'slug'           => 'test-' . uniqid(),
        'name'           => 'Test recomputeAll',
        'type'           => 'questionnaire',
        'scoring_engine' => $engineKey,
        'published'      => true,
    ]);

    makeScoredAttempts($test, 3, null, 55.0);

    $written = NormInterpreter::recomputeAll(3);

    expect($written)->toHaveKey($engineKey);
    expect($written[$engineKey])->toBe(1);
});
