<?php

use App\Models\Test;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestSection;
use App\Models\User;
use Praxis\Plugins\PraxiValeurs\Data\Values;
use Praxis\Plugins\PraxiValeurs\Scoring\SchwartzScoringEngine;

beforeEach(function () {
    $this->engine = new SchwartzScoringEngine();
});

it('returns top 5 values', function () {
    $attempt = valeursAttempt(fn ($q) => 3); // toutes les valeurs ~50
    $r = $this->engine->score($attempt);
    expect($r['top5'])->toHaveCount(5);
});

it('autonomie scores 100 when all autonomie answers max', function () {
    $attempt = valeursAttempt(fn ($q) => $q['dim'] === 'autonomie' ? 6 : 1);
    $r = $this->engine->score($attempt);
    expect($r['dimensions']['autonomie'])->toBe(100);
});

it('all dimensions present in result', function () {
    $attempt = valeursAttempt(fn ($q) => 3);
    $r = $this->engine->score($attempt);
    expect(array_keys($r['dimensions']))->toBe(array_keys(Values::dimensions()));
});

function valeursAttempt(callable $resolver): TestAttempt
{
    $user = User::factory()->create();
    $test = Test::create([
        'slug' => 'praxivaleurs-test-' . uniqid(), 'name' => 't', 'type' => 'questionnaire',
        'scoring_engine' => 'praxivaleurs-schwartz', 'estimated_minutes' => 1, 'published' => true,
    ]);
    $section = TestSection::create(['test_id' => $test->id, 'order' => 1, 'title' => 's']);
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id, 'status' => 'completed',
        'started_at' => now(), 'completed_at' => now(),
    ]);
    foreach (Values::questions() as $i => $q) {
        $tq = TestQuestion::create([
            'section_id' => $section->id, 'order' => $i + 1,
            'type' => 'scale', 'prompt' => $q['texte'],
            'scoring' => ['dimension' => $q['dim'], 'qid' => $q['id'], 'max' => 6],
        ]);
        TestAnswer::create([
            'attempt_id' => $attempt->id, 'question_id' => $tq->id,
            'value' => $resolver($q),
        ]);
    }
    return $attempt->fresh('answers.question');
}
