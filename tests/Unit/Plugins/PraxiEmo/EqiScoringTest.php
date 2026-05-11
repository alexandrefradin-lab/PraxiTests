<?php

use App\Models\Test;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestSection;
use App\Models\User;
use Praxis\Plugins\PraxiEmo\Scoring\EqiScoringEngine;

beforeEach(function () {
    $this->engine = new EqiScoringEngine();
});

it('classifies QE Très élevé when all answers max', function () {
    $attempt = praxiemoAttempt(answer: 4);
    $r = $this->engine->score($attempt);
    expect($r['niveau_qe'])->toBe('QE Très élevé');
    expect($r['score_global'])->toBe(320);
});

it('classifies QE Faible when all answers min', function () {
    $attempt = praxiemoAttempt(answer: 1);
    $r = $this->engine->score($attempt);
    expect($r['niveau_qe'])->toBe('QE Faible');
    expect($r['score_global'])->toBe(80);
});

it('detects strong desirability bias when control items low', function () {
    $attempt = praxiemoAttempt(answer: 4, dsAnswer: 1); // 6 DS items à 1 = sum 6 ≤ 12
    $r = $this->engine->score($attempt);
    expect($r['desirabilite']['niveau'])->toBe('Biais fort');
    expect($r['desirabilite']['alerte'])->toBeTrue();
});

it('returns 3 top forces and 3 top dev (when scores ≤ 12)', function () {
    $attempt = praxiemoAttempt(answer: 1); // tout faible = top_dev rempli
    $r = $this->engine->score($attempt);
    expect($r['top_forces'])->toHaveCount(3);
    expect($r['top_dev'])->toHaveCount(3);
});

function praxiemoAttempt(int $answer, ?int $dsAnswer = null): TestAttempt
{
    $user = User::factory()->create();
    $test = Test::create([
        'slug' => 'praxiemo-test-' . uniqid(), 'name' => 't', 'type' => 'questionnaire',
        'scoring_engine' => 'praxiemo-eqi', 'estimated_minutes' => 1, 'published' => true,
    ]);
    $section = TestSection::create(['test_id' => $test->id, 'order' => 1, 'title' => 's']);
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id, 'status' => 'completed',
        'started_at' => now(), 'completed_at' => now(),
    ]);

    for ($idx = 0; $idx < 86; $idx++) {
        $val = $idx >= 80 && $dsAnswer !== null ? $dsAnswer : $answer;
        $q = TestQuestion::create([
            'section_id' => $section->id, 'order' => $idx + 1,
            'type' => 'scale', 'prompt' => "Q{$idx}",
            'scoring' => ['idx' => $idx],
        ]);
        TestAnswer::create(['attempt_id' => $attempt->id, 'question_id' => $q->id, 'value' => $val]);
    }
    return $attempt->fresh('answers.question');
}
