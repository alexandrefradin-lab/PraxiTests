<?php

use App\Models\Test;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestSection;
use App\Models\User;
use Praxis\Plugins\PraxiCare\Scoring\KarasekMbiScoringEngine;

beforeEach(function () {
    $this->engine = new KarasekMbiScoringEngine();
});

it('detects iso-strain profile (high demands + low latitude + low support)', function () {
    $attempt = praxicareAttempt(
        demandes: array_fill(1, 9, 4),     // max
        latitude: array_fill(1, 9, 1),     // min
        soutien:  array_fill(1, 8, 1),     // min
        ee:       array_fill(1, 9, 0),
        dp:       array_fill(1, 5, 0),
        ap:       array_fill(1, 8, 3),     // après inversion = 0
    );
    $r = $this->engine->score($attempt);
    expect($r['profile'])->toBe('iso_strain');
});

it('detects detendu profile (low demands + high latitude)', function () {
    $attempt = praxicareAttempt(
        demandes: array_fill(1, 9, 1),
        latitude: array_fill(1, 9, 4),
        soutien:  array_fill(1, 8, 4),
        ee: array_fill(1, 9, 0), dp: array_fill(1, 5, 0), ap: array_fill(1, 8, 0),
    );
    $r = $this->engine->score($attempt);
    expect($r['profile'])->toBe('detendu');
});

it('inverts AP scores correctly (high AP raw = low PA score)', function () {
    $attempt = praxicareAttempt(
        demandes: array_fill(1, 9, 2), latitude: array_fill(1, 9, 2), soutien: array_fill(1, 8, 2),
        ee: array_fill(1, 9, 0), dp: array_fill(1, 5, 0),
        ap: array_fill(1, 8, 3),  // raw 3 → after inversion (3-3) = 0 → score 0/24 = forte AP
    );
    $r = $this->engine->score($attempt);
    expect($r['mbi']['ap'])->toBe(0);
    expect($r['mbi']['ap_severite'])->toBe('faible');
});

/* helper */
function praxicareAttempt(array $demandes, array $latitude, array $soutien, array $ee, array $dp, array $ap): TestAttempt
{
    $user = User::factory()->create();
    $test = Test::create([
        'slug' => 'praxicare-test-' . uniqid(), 'name' => 't', 'type' => 'questionnaire',
        'scoring_engine' => 'praxicare-karasek-mbi', 'estimated_minutes' => 1, 'published' => true,
    ]);
    $section = TestSection::create(['test_id' => $test->id, 'order' => 1, 'title' => 's']);
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id, 'status' => 'completed',
        'started_at' => now(), 'completed_at' => now(),
        'progress' => ['has_superior' => true],
    ]);

    $maps = [
        'D' => $demandes, 'L' => $latitude, 'S' => $soutien,
        'EE' => $ee, 'DP' => $dp, 'AP' => $ap,
    ];

    $order = 0;
    foreach ($maps as $prefix => $values) {
        foreach ($values as $i => $val) {
            $key = $prefix . $i;
            $q = TestQuestion::create([
                'section_id' => $section->id, 'order' => ++$order,
                'type' => 'scale', 'prompt' => $key,
                'scoring' => ['key' => $key],
            ]);
            TestAnswer::create(['attempt_id' => $attempt->id, 'question_id' => $q->id, 'value' => $val]);
        }
    }
    return $attempt->fresh('answers.question');
}
