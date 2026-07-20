<?php

/**
 * Tests Feature sécurité — 6 cas prioritaires identifiés dans l'audit.
 *
 * 1. Un utilisateur ne peut pas répondre à une question d'un autre test.
 * 2. Un utilisateur ne peut pas compléter une tentative incomplète.
 * 3. Une tentative completed refuse toute nouvelle réponse.
 * 4. Un professionnel A ne voit pas les leads du professionnel B.
 * 5. Un plugin manifest invalide est rejeté sans exception fatale.
 * 6. L'installeur retourne 403 (neutralisé en production).
 */

use App\Models\Lead;
use App\Models\Profile;
use App\Models\ProfessionalAccount;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use App\Models\TestResult;
use App\Models\TestSection;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Praxis\Core\Plugins\PluginManifestValidator;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Queue::fake();
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

function secMakeTest(int $questions = 3, bool $required = true): Test
{
    $test = Test::create([
        'slug'             => 'sec-test-' . uniqid(),
        'name'             => 'Security Test',
        'type'             => 'questionnaire',
        'scoring_engine'   => 'default',
        'estimated_minutes'=> 5,
        'published'        => true,
    ]);
    $section = TestSection::create(['test_id' => $test->id, 'title' => 'S', 'order' => 0]);
    for ($i = 0; $i < $questions; $i++) {
        TestQuestion::create([
            'section_id' => $section->id,
            'type'       => 'scale',
            'prompt'     => "Q{$i}",
            'order'      => $i,
            'options'    => ['min' => 1, 'max' => 5],
            'required'   => $required,
        ]);
    }
    return $test->fresh('sections.questions');
}

function secMakeAttempt(User $user, Test $test, string $status = 'in_progress'): TestAttempt
{
    return TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => $status,
        'started_at'       => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);
}

function secMakeUserWithProfile(): User
{
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();
    return $user;
}

function secMakeProfessional(): User
{
    Role::firstOrCreate(['name' => 'professional', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('professional');
    return $user;
}

// ─── 1. Injection de question cross-test ─────────────────────────────────────

it('rejects an answer for a question belonging to a different test', function () {
    $user    = secMakeUserWithProfile();
    $testA   = secMakeTest();
    $testB   = secMakeTest();
    $attempt = secMakeAttempt($user, $testA);

    // Question issue du test B, pas du test A
    $foreignQuestion = $testB->sections->first()->questions->first();

    $this->actingAs($user)
        ->post(route('attempt.answer', $attempt), [
            'question_id' => $foreignQuestion->id,
            'value'       => 3,
        ])
        ->assertStatus(422);

    // Aucune réponse ne doit être enregistrée
    expect($attempt->answers()->count())->toBe(0);
});

// ─── 2. Complétion d'une tentative incomplète refusée ────────────────────────

it('refuses to complete an attempt when required questions are unanswered', function () {
    $user    = secMakeUserWithProfile();
    $test    = secMakeTest(3, required: true); // 3 questions obligatoires
    $attempt = secMakeAttempt($user, $test);

    // Répondre à seulement 1 question sur 3
    $question = $test->sections->first()->questions->first();
    $this->actingAs($user)->post(route('attempt.answer', $attempt), [
        'question_id' => $question->id,
        'value'       => 3,
    ]);

    // La complétion doit être refusée
    $this->actingAs($user)
        ->post(route('attempt.complete', $attempt))
        ->assertSessionHasErrors('complete');

    $attempt->refresh();
    expect($attempt->status)->toBe('in_progress');
});

it('allows completing an attempt when all required questions are answered', function () {
    $user    = secMakeUserWithProfile();
    $test    = secMakeTest(2, required: true);
    $attempt = secMakeAttempt($user, $test);

    // Répondre à toutes les questions
    foreach ($test->sections->first()->questions as $question) {
        $this->actingAs($user)->post(route('attempt.answer', $attempt), [
            'question_id' => $question->id,
            'value'       => 4,
        ]);
    }

    $this->actingAs($user)
        ->post(route('attempt.complete', $attempt))
        ->assertRedirect();

    $attempt->refresh();
    expect($attempt->status)->toBe('completed');
});

// ─── 3. Réponse après completion refusée ─────────────────────────────────────

it('rejects a new answer on an already completed attempt', function () {
    $user    = secMakeUserWithProfile();
    $test    = secMakeTest();
    $attempt = secMakeAttempt($user, $test, 'completed');

    // Créer un résultat pour que l'état soit cohérent
    TestResult::create([
        'attempt_id'   => $attempt->id,
        'scoring'      => [],
        'generated_at' => now(),
    ]);

    $question = $test->sections->first()->questions->first();

    $this->actingAs($user)
        ->post(route('attempt.answer', $attempt), [
            'question_id' => $question->id,
            'value'       => 5,
        ])
        ->assertStatus(422);

    expect($attempt->answers()->count())->toBe(0);
});

// ─── 4. Cloisonnement multi-tenant leads ─────────────────────────────────────

it('prevents professional A from seeing leads of professional B', function () {
    $proA = secMakeProfessional();
    $proB = secMakeProfessional();

    $accountA = ProfessionalAccount::create(['owner_user_id' => $proA->id, 'company_name' => 'Cabinet A']);
    $accountB = ProfessionalAccount::create(['owner_user_id' => $proB->id, 'company_name' => 'Cabinet B']);

    $proA->professionalAccounts()->attach($accountA->id, ['role' => 'owner']);
    $proB->professionalAccounts()->attach($accountB->id, ['role' => 'owner']);

    $leadOfB = Lead::create([
        'professional_account_id' => $accountB->id,
        'email'                   => 'candidate@example.com',
        'first_name'              => 'Jean',
        'last_name'               => 'Dupont',
        'status'                  => 'new',
    ]);

    // Pro A ne doit pas voir le lead de B dans la liste
    $response = $this->actingAs($proA)->get(route('admin.leads.index'));
    $response->assertOk();

    // Le lead de B ne doit pas apparaître dans les données Inertia
    $response->assertInertia(fn ($page) => $page
        ->where('leads.data', fn ($leads) =>
            collect($leads)->every(fn ($l) => $l['id'] !== $leadOfB->id)
        )
    );
});

it('prevents professional A from accessing the lead show page of professional B', function () {
    $proA = secMakeProfessional();
    $proB = secMakeProfessional();

    $accountA = ProfessionalAccount::create(['owner_user_id' => $proA->id, 'company_name' => 'Cabinet A2']);
    $accountB = ProfessionalAccount::create(['owner_user_id' => $proB->id, 'company_name' => 'Cabinet B2']);

    $proA->professionalAccounts()->attach($accountA->id, ['role' => 'member']);
    $proB->professionalAccounts()->attach($accountB->id, ['role' => 'owner']);

    $leadOfB = Lead::create([
        'professional_account_id' => $accountB->id,
        'email'                   => 'other@example.com',
        'status'                  => 'new',
    ]);

    $this->actingAs($proA)
        ->get(route('admin.leads.show', $leadOfB))
        ->assertForbidden();
});

// ─── 5. Plugin manifest invalide rejeté sans crash ───────────────────────────

it('throws InvalidArgumentException for a plugin manifest missing required keys', function () {
    expect(fn () => PluginManifestValidator::validate([
        'slug'    => 'bad-plugin',
        'name'    => 'Bad Plugin',
        'version' => '1.0.0',
        // 'type', 'service_provider' manquants
    ], 'test'))->toThrow(\InvalidArgumentException::class);
});

it('throws for an invalid plugin slug', function () {
    expect(fn () => PluginManifestValidator::validate([
        'slug'             => 'Bad Plugin Slug!',
        'name'             => 'Bad',
        'version'          => '1.0.0',
        'type'             => 'test',
        'service_provider' => 'Foo\\Bar\\Provider',
    ], 'test'))->toThrow(\InvalidArgumentException::class);
});

it('throws for a non-semver version', function () {
    expect(fn () => PluginManifestValidator::validate([
        'slug'             => 'good-slug',
        'name'             => 'Good',
        'version'          => 'not-semver',
        'type'             => 'test',
        'service_provider' => 'Foo\\Bar\\Provider',
    ], 'test'))->toThrow(\InvalidArgumentException::class);
});

it('accepts a valid plugin manifest', function () {
    expect(fn () => PluginManifestValidator::validate([
        'slug'             => 'praxilink',
        'name'             => 'L\'Art des Liens',
        'version'          => '1.0.1',
        'type'             => 'mini-app',
        'namespace'        => 'Praxis\\Plugins\\PraxiLink',
        'service_provider' => 'Praxis\\Plugins\\PraxiLink\\PluginServiceProvider',
        'permissions'      => [],
    ], 'test'))->not->toThrow(\InvalidArgumentException::class);
});

// ─── 6. Installeur neutralisé ────────────────────────────────────────────────

it('returns 403 on the install endpoint', function () {
    $this->get('/install.php')->assertStatus(403);
});
