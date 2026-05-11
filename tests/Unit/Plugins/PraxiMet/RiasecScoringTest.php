<?php

use App\Models\Test;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestSection;
use App\Models\User;
use Praxis\Plugins\PraxiMet\Scoring\RiasecScoringEngine;

beforeEach(function () {
    $this->engine = new RiasecScoringEngine();
});

it('returns key praximet-riasec', function () {
    expect($this->engine->key())->toBe('praximet-riasec');
});

it('computes correct RIASEC code for a clear Realiste profile', function () {
    $attempt = makeAttemptWithAnswers([
        // 14 R answers (max for R), 0 elsewhere
        'R' => 14, 'I' => 2, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0,
    ]);
    $score = $this->engine->score($attempt);
    expect($score['code'])->toStartWith('R');
    expect($score['raw_scores']['R'])->toBe(14);
});

it('respects RIASEC standard order on ties', function () {
    // R=I=A=S=E=C all equal → code starts with R then I then A
    $attempt = makeAttemptWithAnswers(['R' => 7, 'I' => 7, 'A' => 7, 'S' => 7, 'E' => 7, 'C' => 7]);
    $score = $this->engine->score($attempt);
    expect($score['code'])->toBe('RIA');
});

it('normalizes scores to percentage on max 14', function () {
    $attempt = makeAttemptWithAnswers(['R' => 7, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0]);
    $score = $this->engine->score($attempt);
    expect($score['dimensions']['R'])->toBe(50.0);
});

/* ---------------- Helpers ---------------- */

function makeAttemptWithAnswers(array $byType): TestAttempt
{
    $user = User::factory()->create();
    $test = Test::create([
        'slug' => 'praximet-test-' . uniqid(),
        'name' => 'Test', 'type' => 'questionnaire',
        'scoring_engine' => 'praximet-riasec', 'estimated_minutes' => 1, 'published' => true,
    ]);
    $section = TestSection::create(['test_id' => $test->id, 'order' => 1, 'title' => 's']);
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id, 'status' => 'completed',
        'started_at' => now(), 'completed_at' => now(),
    ]);

    foreach (['R', 'I', 'A', 'S', 'E', 'C'] as $type) {
        $count = $byType[$type] ?? 0;
        // crée 14 questions par type, dont $count à 1 et le reste à 0
        for ($i = 1; $i <= 14; $i++) {
            $q = TestQuestion::create([
                'section_id' => $section->id, 'order' => $i,
                'type' => 'single', 'prompt' => "{$type}{$i}",
                'options' => [['value' => 1, 'label' => 'Oui'], ['value' => 0, 'label' => 'Non']],
                'scoring' => ['rid' => $type . $i, 'dimension' => $type, 'max' => 1],
            ]);
            TestAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $q->id,
                'value' => $i <= $count ? 1 : 0,
            ]);
        }
    }
    return $attempt->fresh('answers.question');
}
