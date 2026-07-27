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

/** Faux PDF avec de vrais magic bytes : la validation finfo (CvUploadRequest) rejette les fakes remplis de zéros. */
function onboardingFakePdf(string $name = 'cv.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< >>\n%%EOF\n");
}

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
    $cv   = onboardingFakePdf('mon-cv.pdf');

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'             => 'employee',
            'status_since'       => now()->subMonths(6)->format('Y-m-d'),
            'current_role'       => 'Développeur',
            'industry'           => 'Tech',
            'problematique'      => 'Faire le point sur ma carrière.',
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

it('stores optional age band and education level for norming', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'          => 'employee',
            'status_since'    => now()->subMonths(6)->format('Y-m-d'),
            'age_band'        => '35-44',
            'education_level' => 'bac_5_plus',
            'problematique'   => 'Faire le point.',
            'cv'              => onboardingFakePdf(),
            'consent_data'    => '1',
        ])
        ->assertRedirect(route('tests.index'));

    $profile = $user->fresh()->profile;
    expect($profile->age_band)->toBe('35-44');
    expect($profile->education_level)->toBe('bac_5_plus');
    expect($profile->normGroupKey())->toBe('age:35-44');
});

it('accepts onboarding without age band or education level (optional fields)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'        => 'employee',
            'status_since'  => now()->subMonths(6)->format('Y-m-d'),
            'age_band'      => '',
            'problematique' => 'Faire le point.',
            'cv'            => onboardingFakePdf(),
            'consent_data'  => '1',
        ])
        ->assertRedirect(route('tests.index'));

    $profile = $user->fresh()->profile;
    expect($profile->age_band)->toBeNull();
    expect($profile->normGroupKey())->toBeNull();
});

it('rejects an unknown age band', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'        => 'employee',
            'status_since'  => now()->subMonths(6)->format('Y-m-d'),
            'age_band'      => 'not-a-band',
            'problematique' => 'Faire le point.',
            'cv'            => onboardingFakePdf(),
            'consent_data'  => '1',
        ])
        ->assertSessionHasErrors('age_band');
});

it('dispatches CV extraction job after profile creation', function () {
    $user = User::factory()->create();
    $cv   = onboardingFakePdf();

    $this->actingAs($user)->post(route('onboarding.store'), [
        'status'        => 'jobseeker',
        'status_since'  => now()->subYear()->format('Y-m-d'),
        'problematique' => 'Retrouver un emploi qui me correspond.',
        'cv'            => $cv,
        'consent_data'  => '1',
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

    $newCv = onboardingFakePdf('nouveau-cv.pdf');

    $this->actingAs($user)->put(route('profile.update'), [
        'status'       => 'employee',
        'status_since' => now()->subMonths(12)->format('Y-m-d'),
        'cv'           => $newCv,
    ]);

    Queue::assertPushed(ExtractCvDataJob::class);
});
