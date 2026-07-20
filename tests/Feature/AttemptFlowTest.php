<?php

use App\Jobs\GenerateAttemptInsights;
use App\Models\Profile;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestResult;
use App\Models\TestSection;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Crée un test publié minimal avec 1 section et N questions scale.
 */
function makePublishedTest(int $questionCount = 3): Test
{
    $test = Test::create([
        'slug'            => 'test-feature-' . uniqid(),
        'name'            => 'Test Feature',
        'type'            => 'questionnaire',
        'scoring_engine'  => 'default',
        'estimated_minutes' => 5,
        'published'       => true,
    ]);

    $section = TestSection::create([
        'test_id' => $test->id,
        'title'   => 'Section test',
        'order'   => 0,
    ]);

    for ($i = 0; $i < $questionCount; $i++) {
        TestQuestion::create([
            'section_id' => $section->id,
            'type'       => 'scale',
            'prompt'     => "Question {$i}",
            'order'      => $i,
            'options'    => ['min' => 1, 'max' => 5],
            'required'   => true,
        ]);
    }

    return $test->fresh('sections.questions');
}

/**
 * Crée un utilisateur avec un profil complet (sans upload réel de CV).
 */
function makeUserWithProfile(): User
{
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();
    return $user;
}

// ─── Accès & guards ───────────────────────────────────────────────────────────

it('redirects guests trying to start a test', function () {
    $test = makePublishedTest();
    $this->post(route('attempt.start', $test->slug))->assertRedirect(route('login'));
});

it('blocks user with incomplete profile from starting a test', function () {
    $user = User::factory()->create(); // profil absent
    $test = makePublishedTest();

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertForbidden();
});

it('returns 404 for unpublished test', function () {
    $user = makeUserWithProfile();
    $test = makePublishedTest();
    $test->update(['published' => false]);

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertNotFound();
});

// ─── Démarrer une tentative ───────────────────────────────────────────────────

it('creates an attempt when user starts a test', function () {
    $user = makeUserWithProfile();
    $test = makePublishedTest();

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect();

    expect(TestAttempt::where('user_id', $user->id)->where('test_id', $test->id)->exists())->toBeTrue();
});

it('resumes existing in_progress attempt instead of creating a new one', function () {
    $user    = makeUserWithProfile();
    $test    = makePublishedTest();
    $attempt = TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'in_progress',
        'started_at'       => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);

    $this->actingAs($user)
        ->post(route('attempt.start', $test->slug))
        ->assertRedirect(route('attempt.show', $attempt));

    // Une seule tentative en base
    expect(TestAttempt::where('user_id', $user->id)->where('test_id', $test->id)->count())->toBe(1);
});

// ─── Répondre aux questions ───────────────────────────────────────────────────

it('records an answer for the current attempt', function () {
    $user    = makeUserWithProfile();
    $test    = makePublishedTest(3);
    $attempt = TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'in_progress',
        'started_at'       => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);

    $question = $test->sections->first()->questions->first();

    $this->actingAs($user)
        ->post(route('attempt.answer', $attempt), [
            'question_id' => $question->id,
            'value'       => 4,
            'time_spent'  => 8,
        ])
        ->assertRedirect();

    expect($attempt->answers()->where('question_id', $question->id)->exists())->toBeTrue();
    // Le cast 'array' de TestAnswer.value redonne l'entier envoyé.
    expect($attempt->answers()->where('question_id', $question->id)->value('value'))->toBe(4);
});

it('blocks answering on another user attempt', function () {
    $owner   = makeUserWithProfile();
    $other   = User::factory()->create();
    $test    = makePublishedTest();
    $attempt = TestAttempt::create([
        'user_id' => $owner->id, 'test_id' => $test->id,
        'status' => 'in_progress', 'started_at' => now(), 'last_activity_at' => now(), 'progress' => [],
    ]);
    $question = $test->sections->first()->questions->first();

    $this->actingAs($other)
        ->post(route('attempt.answer', $attempt), ['question_id' => $question->id, 'value' => 3])
        ->assertForbidden();
});

// ─── Compléter et résultats ───────────────────────────────────────────────────

it('completes an attempt and creates a result', function () {
    $user    = makeUserWithProfile();
    $test    = makePublishedTest(2);
    $attempt = TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'in_progress',
        'started_at'       => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);

    // Répondre à toutes les questions obligatoires (garde audit risque #3)
    foreach ($test->sections->first()->questions as $q) {
        $attempt->answers()->create(['question_id' => $q->id, 'value' => 3, 'time_spent_seconds' => 5]);
    }

    $this->actingAs($user)
        ->post(route('attempt.complete', $attempt))
        ->assertRedirect();

    $attempt->refresh();
    expect($attempt->status)->toBe('completed');
    expect($attempt->result)->not->toBeNull();
    expect($attempt->result->scoring)->not->toBeNull();
});

it('dispatches GenerateAttemptInsights job after completion', function () {
    $user    = makeUserWithProfile();
    $test    = makePublishedTest();
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id,
        'status' => 'in_progress', 'started_at' => now(), 'last_activity_at' => now(), 'progress' => [],
    ]);

    // Répondre à toutes les questions obligatoires (garde audit risque #3)
    foreach ($test->sections->first()->questions as $q) {
        $attempt->answers()->create(['question_id' => $q->id, 'value' => 3, 'time_spent_seconds' => 5]);
    }

    $this->actingAs($user)->post(route('attempt.complete', $attempt));

    Queue::assertPushed(GenerateAttemptInsights::class, fn ($job) => $job->attemptId === $attempt->id);
});

it('shows result page after completion', function () {
    $user    = makeUserWithProfile();
    $test    = makePublishedTest();
    $attempt = TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id,
        'status' => 'completed', 'started_at' => now(),
        'completed_at' => now(), 'last_activity_at' => now(), 'progress' => [],
    ]);
    TestResult::create([
        'attempt_id'  => $attempt->id,
        'scoring'     => ['dimensions' => []],
        'generated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('results.show', $attempt))
        ->assertInertia(fn ($page) => $page->component('Candidate/ResultsShow')
            ->has('attempt')
            ->has('result')
        );
});

it('blocks viewing another user result', function () {
    $owner  = makeUserWithProfile();
    $other  = User::factory()->create();
    $test   = makePublishedTest();
    $attempt = TestAttempt::create([
        'user_id' => $owner->id, 'test_id' => $test->id,
        'status' => 'completed', 'started_at' => now(),
        'completed_at' => now(), 'last_activity_at' => now(), 'progress' => [],
    ]);

    $this->actingAs($other)
        ->get(route('results.show', $attempt))
        ->assertForbidden();
});

// ─── Historique ───────────────────────────────────────────────────────────────

it('shows history page with user attempts', function () {
    $user = makeUserWithProfile();
    $test = makePublishedTest();
    TestAttempt::create([
        'user_id' => $user->id, 'test_id' => $test->id,
        'status' => 'completed', 'started_at' => now(),
        'completed_at' => now(), 'last_activity_at' => now(), 'progress' => [],
    ]);

    $this->actingAs($user)
        ->get(route('history'))
        ->assertInertia(fn ($page) => $page
            ->component('Candidate/History')
            ->has('attempts', 1)
        );
});
