<?php

use App\Models\Profile;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

// ─── Export RGPD (Art. 15) ────────────────────────────────────────────────────

it('requires authentication to access RGPD page', function () {
    $this->get(route('gdpr.show'))->assertRedirect(route('login'));
    $this->get(route('gdpr.export'))->assertRedirect(route('login'));
});

it('returns JSON export for authenticated user', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();

    $response = $this->actingAs($user)
        ->get(route('gdpr.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json');

    $data = $response->json();
    expect($data)->toHaveKey('account')
        ->toHaveKey('profile')
        ->toHaveKey('test_results')
        ->toHaveKey('export_scope');

    // Les données doivent appartenir au user connecté
    expect($data['account']['email'])->toBe($user->email);
    expect($data['account']['id'])->toBe($user->id);
});

it('does not leak data of another user in the export', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $this->actingAs($userA)
        ->get(route('gdpr.export'))
        ->assertJson(fn ($json) => $json
            ->where('account.email', $userA->email)
            ->etc()
        );

    // Email de B ne doit pas apparaître dans l'export de A
    $response = $this->actingAs($userA)->get(route('gdpr.export'));
    expect($response->content())->not->toContain($userB->email);
});

it('includes AI disclaimer in the export when result exists', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();

    $test = Test::create(['slug' => 'gdpr-test-' . uniqid(), 'name' => 'Test RGPD', 'type' => 'questionnaire', 'scoring_engine' => 'default', 'estimated_minutes' => 5, 'published' => true]);
    $attempt = TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'completed',
        'started_at'       => now(),
        'completed_at'     => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);
    TestResult::create([
        'attempt_id'  => $attempt->id,
        'scoring'     => ['dimensions' => ['motivation' => 4, 'competences' => 3]],
        'generated_at' => now(),
        'ai_driver'   => 'anthropic',
        'ai_model'    => 'claude-sonnet-4-6',
    ]);

    $response = $this->actingAs($user)->get(route('gdpr.export'));
    $data     = $response->json();

    expect($data['test_results'])->toBeArray();
    $result = $data['test_results'][0];
    expect($result)->toHaveKey('ai_disclaimer');
    expect($result['ai_disclaimer'])->toHaveKey('disclaimer_text');
    expect($result['ai_disclaimer']['model'])->toBe('claude-sonnet-4-6');
});

// ─── Suppression de compte (Art. 17) ─────────────────────────────────────────

it('requires authentication to delete account', function () {
    $this->delete(route('gdpr.destroy'))->assertRedirect(route('login'));
});

it('requires password confirmation to delete account', function () {
    $user = User::factory()->create(['password' => Hash::make('correct-password')]);

    $this->actingAs($user)
        ->delete(route('gdpr.destroy'), ['password' => 'wrong-password'])
        ->assertSessionHasErrors('password');

    // User toujours en base
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

it('deletes user account and all personal data on correct password', function () {
    $user    = User::factory()->create(['password' => Hash::make('secret123')]);
    $profile = Profile::factory()->for($user)->cvUploaded()->create();

    // Simuler un fichier CV existant
    Storage::disk('local')->put($profile->cv_path, 'fake cv content');
    Storage::disk('local')->assertExists($profile->cv_path);

    $this->actingAs($user)
        ->delete(route('gdpr.destroy'), ['password' => 'secret123'])
        ->assertRedirect('/');

    // Compte supprimé
    $this->assertDatabaseMissing('users', ['id' => $user->id]);

    // Profil supprimé
    $this->assertDatabaseMissing('profiles', ['user_id' => $user->id]);

    // Fichier CV supprimé du storage
    Storage::disk('local')->assertMissing($profile->cv_path);
});

it('deletes associated test attempts and results when account is deleted', function () {
    $user    = User::factory()->create(['password' => Hash::make('pass1234')]);
    Profile::factory()->for($user)->cvUploaded()->create();

    $test    = Test::create(['slug' => 'del-test-' . uniqid(), 'name' => 'Test Del', 'type' => 'questionnaire', 'scoring_engine' => 'default', 'estimated_minutes' => 5, 'published' => true]);
    $attempt = TestAttempt::create(['user_id' => $user->id, 'test_id' => $test->id, 'status' => 'completed', 'started_at' => now(), 'completed_at' => now(), 'last_activity_at' => now(), 'progress' => []]);
    $result  = TestResult::create(['attempt_id' => $attempt->id, 'scoring' => [], 'generated_at' => now()]);

    $this->actingAs($user)
        ->delete(route('gdpr.destroy'), ['password' => 'pass1234']);

    $this->assertDatabaseMissing('test_attempts', ['id' => $attempt->id]);
    $this->assertDatabaseMissing('test_results',  ['id' => $result->id]);
});

it('logs out the user after account deletion', function () {
    $user = User::factory()->create(['password' => Hash::make('pass1234')]);

    $response = $this->actingAs($user)
        ->delete(route('gdpr.destroy'), ['password' => 'pass1234']);

    $this->assertGuest();
});
