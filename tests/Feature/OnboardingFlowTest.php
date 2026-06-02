<?php

use App\Jobs\ExtractCvDataJob;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

// ─── Accès ───────────────────────────────────────────────────────────────────

it('redirects guests from onboarding', function () {
    $this->get(route('onboarding.show'))->assertRedirect(route('login'));
});

it('shows onboarding form to authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('onboarding.show'))
        ->assertInertia(fn ($page) => $page->component('Candidate/Onboarding'));
});

// ─── Création du profil ───────────────────────────────────────────────────────

it('creates a profile on first onboarding submission', function () {
    $user = User::factory()->create();
    $cv   = UploadedFile::fake()->create('mon-cv.pdf', 200, 'application/pdf');

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'             => 'employee',
            'status_since'       => now()->subMonths(6)->format('Y-m-d'),
            'current_role'       => 'Développeur',
            'industry'           => 'Tech',
            'cv'                 => $cv,
            'consent_data'       => '1',
            'consent_marketing'  => false,
        ])
        ->assertRedirect(route('tests.index'));

    $profile = $user->fresh()->profile;
    expect($profile)->not->toBeNull();
    expect($profile->status)->toBe('employee');
    expect($profile->cv_path)->not->toBeNull();
    expect($profile->consent_data)->toBeTrue();
    expect($profile->isComplete())->toBeTrue();
    Storage::disk('local')->assertExists($profile->cv_path);
});

it('dispatches CV extraction job after profile creation', function () {
    $user = User::factory()->create();
    $cv   = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');

    $this->actingAs($user)->post(route('onboarding.store'), [
        'status'       => 'jobseeker',
        'status_since' => now()->subYear()->format('Y-m-d'),
        'cv'           => $cv,
        'consent_data' => '1',
    ]);

    Queue::assertPushed(ExtractCvDataJob::class);
});

it('rejects onboarding without cv', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'       => 'employee',
            'status_since' => now()->subMonths(3)->format('Y-m-d'),
            'consent_data' => '1',
        ])
        ->assertSessionHasErrors('cv');
});

it('rejects onboarding without consent_data', function () {
    $user = User::factory()->create();
    $cv   = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'       => 'employee',
            'status_since' => now()->subMonths(3)->format('Y-m-d'),
            'cv'           => $cv,
            // consent_data absent
        ])
        ->assertSessionHasErrors('consent_data');
});

// ─── Édition du profil ────────────────────────────────────────────────────────

it('shows profile edit page', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn ($page) => $page->component('Candidate/Onboarding'));
});

it('updates profile without requiring new cv', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();

    $oldCvPath = $user->profile->cv_path;

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'status'       => 'entrepreneur',
            'status_since' => now()->subMonths(2)->format('Y-m-d'),
            'current_role' => 'Fondateur',
        ])
        ->assertRedirect(route('tests.index'));

    $profile = $user->fresh()->profile;
    expect($profile->status)->toBe('entrepreneur');
    expect($profile->current_role)->toBe('Fondateur');
    expect($profile->cv_path)->toBe($oldCvPath); // inchangé
});

it('replaces cv and re-dispatches extraction job when new cv uploaded on update', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();

    $newCv = UploadedFile::fake()->create('nouveau-cv.pdf', 150, 'application/pdf');

    $this->actingAs($user)->put(route('profile.update'), [
        'status'       => 'employee',
        'status_since' => now()->subMonths(12)->format('Y-m-d'),
        'cv'           => $newCv,
    ]);

    Queue::assertPushed(ExtractCvDataJob::class);
});
