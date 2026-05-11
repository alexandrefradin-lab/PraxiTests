<?php

use App\Models\Test;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestSection;
use App\Models\User;
use Praxis\Plugins\PraxiMum\Data\Catalog;
use Praxis\Plugins\PraxiMum\Scoring\BigFiveScoringEngine;

beforeEach(function () {
    $this->engine = new BigFiveScoringEngine();
});

it('returns key praximum-bigfive', function () {
    expect($this->engine->key())->toBe('praximum-bigfive');
});

it('catalogs 128 questions', function () {
    expect(Catalog::questions())->toHaveCount(128);
});

it('produces 5 dimensions and 30 facettes', function () {
    expect(Catalog::dimensions())->toHaveCount(5);
    expect(Catalog::facettes())->toHaveCount(30);
});

it('inverts answers correctly', function () {
    $attempt = praximumAttempt(answerResolver: fn ($q) => $q['inv'] ? 1 : 4);
    $r = $this->engine->score($attempt);
    // Pour un question inversé répondu 1 → effectif = 5-1 = 4
    // Donc tous les scores devraient être très hauts.
    foreach ($r['scores_dim'] as $d) {
        expect($d['T'])->toBeGreaterThanOrEqual(50);
    }
});

it('clamps T scores between 20 and 80', function () {
    $attempt = praximumAttempt(answerResolver: fn ($q) => 4);
    $r = $this->engine->score($attempt);
    foreach ($r['scores_facette'] as $f) {
        expect($f['T'])->toBeGreaterThanOrEqual(20)->toBeLessThanOrEqual(80);
    }
});

function praximumAttempt(callable $answerResolver): TestAttempt
{
    $user = User::factory()->create();
    $test = Test::create([
        'slug' => 'praximum-test-' . uniqid(), 'name' => 't', 'type' => 'questionnaire',
        'scoring_engine' => 'praximum-bigfive', 'estimated_minutes' => 1, 'published' => true,
    ]);
    $section = TestSection::create(['test_id' => $test->id, 'order' => 1, 'title' => 's']);
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id, 'status' => 'completed',
        'started_at' => now(), 'completed_at' => now(),
    ]);
    foreach (Catalog::questions() as $i => $q) {
        $tq = TestQuestion::create([
            'section_id' => $section->id, 'order' => $i + 1,
            'type' => 'scale', 'prompt' => $q['texte'],
            'scoring' => [
                'qid' => $q['id'], 'dim' => $q['dim'],
                'facette' => $q['facette'] ?? null, 'inv' => $q['inv'],
            ],
        ]);
        TestAnswer::create([
            'attempt_id' => $attempt->id, 'question_id' => $tq->id,
            'value' => $answerResolver($q),
        ]);
    }
    return $attempt->fresh('answers.question');
}
