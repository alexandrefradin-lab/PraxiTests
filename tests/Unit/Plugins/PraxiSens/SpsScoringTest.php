<?php

use App\Models\Test;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestSection;
use App\Models\User;
use Praxis\Plugins\PraxiSens\Scoring\PraxiSensScoringEngine;

beforeEach(function () {
    $this->engine = new PraxiSensScoringEngine();
});

it('classifies Haute sensibilité marquée when all answers max', function () {
    $attempt = praxisensAttempt(answer: 5, ctrlAnswer: 5);
    $r = $this->engine->score($attempt);
    expect($r['global_label'])->toBe('Haute sensibilité marquée');
    expect($r['global_score'])->toBe(100);
});

it('classifies Sensibilité faible when all answers min', function () {
    $attempt = praxisensAttempt(answer: 1, ctrlAnswer: 5);
    $r = $this->engine->score($attempt);
    expect($r['global_label'])->toBe('Sensibilité faible');
    expect($r['global_score'])->toBe(0);
});

it('detects strong desirability bias when control items low', function () {
    $attempt = praxisensAttempt(answer: 5, ctrlAnswer: 1); // 6 items ctrl à 1 = sum 6 ≤ 12
    $r = $this->engine->score($attempt);
    expect($r['desirabilite']['niveau'])->toBe('Biais fort');
    expect($r['desirabilite']['alerte'])->toBeTrue();
});

it('shrinks dimension scores toward midpoint under strong bias', function () {
    $attempt = praxisensAttempt(answer: 5, ctrlAnswer: 1);
    $r = $this->engine->score($attempt);
    // Moyenne 5 régressée vers 3 (facteur 0.80) : 3 + 2×0.8 = 4.6 → 90 %.
    expect($r['global_score'])->toBe(90);
    foreach ($r['dimensions'] as $pct) {
        expect($pct)->toBe(90);
    }
});

it('reports reliable answers when control items admit normal flaws', function () {
    $attempt = praxisensAttempt(answer: 3, ctrlAnswer: 4); // sum 24 > 18 = Fiable
    $r = $this->engine->score($attempt);
    expect($r['desirabilite']['niveau'])->toBe('Fiable');
    expect($r['desirabilite']['alerte'])->toBeFalse();
});

it('marks desirability as non mesuré on attempts without control items', function () {
    $attempt = praxisensAttempt(answer: 5); // anciennes passations : pas d'items ctrl
    $r = $this->engine->score($attempt);
    expect($r['desirabilite']['niveau'])->toBe('Non mesuré');
    expect($r['desirabilite']['alerte'])->toBeFalse();
    expect($r['global_score'])->toBe(100); // aucune correction appliquée
});

function praxisensAttempt(int $answer, ?int $ctrlAnswer = null): TestAttempt
{
    $user = User::factory()->create();
    $test = Test::create([
        'slug' => 'praxisens-test-' . uniqid(), 'name' => 't', 'type' => 'questionnaire',
        'scoring_engine' => 'praxisens-sps', 'estimated_minutes' => 1, 'published' => true,
    ]);
    $section = TestSection::create(['test_id' => $test->id, 'order' => 1, 'title' => 's']);
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id, 'status' => 'completed',
        'started_at' => now(), 'completed_at' => now(),
    ]);

    // 24 items sensoriels (6 par dimension) + 6 items de contrôle (si demandés).
    $dimensions = ['eoe', 'aes', 'lst', 'emo'];
    $order = 0;
    foreach ($dimensions as $dim) {
        for ($i = 0; $i < 6; $i++) {
            $q = TestQuestion::create([
                'section_id' => $section->id, 'order' => ++$order,
                'type' => 'scale', 'prompt' => "Q{$order}",
                'scoring' => ['dimension' => $dim, 'weight' => 1],
            ]);
            TestAnswer::create(['attempt_id' => $attempt->id, 'question_id' => $q->id, 'value' => $answer]);
        }
    }
    if ($ctrlAnswer !== null) {
        for ($i = 0; $i < 6; $i++) {
            $q = TestQuestion::create([
                'section_id' => $section->id, 'order' => ++$order,
                'type' => 'scale', 'prompt' => "Ctrl{$i}",
                'scoring' => ['dimension' => 'ctrl', 'weight' => 1],
            ]);
            TestAnswer::create(['attempt_id' => $attempt->id, 'question_id' => $q->id, 'value' => $ctrlAnswer]);
        }
    }
    return $attempt->fresh('answers.question');
}
