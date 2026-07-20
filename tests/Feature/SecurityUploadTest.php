<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

/** Faux PDF avec de vrais magic bytes : la validation finfo (CvUploadRequest) rejette les fakes remplis de zéros. */
function secUploadFakePdf(string $name = 'cv.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< >>\n%%EOF\n");
}

// ─── Validation MIME ──────────────────────────────────────────────────────────

it('rejects a file with an invalid extension as CV', function () {
    $user = User::factory()->create();
    $exe  = UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream');

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'       => 'employee',
            'status_since' => now()->subMonths(6)->format('Y-m-d'),
            'cv'           => $exe,
            'consent_data' => '1',
        ])
        ->assertSessionHasErrors('cv');
});

it('rejects a file with a MIME type disguised as PDF', function () {
    $user = User::factory()->create();

    // Fichier texte renommé en .pdf (MIME navigateur spoofé mais extension cohérente)
    // Laravel valide le MIME réel via finfo — ce test vérifie que l'extension seule ne suffit pas
    $fake = UploadedFile::fake()->create('cv.pdf', 10, 'text/html');

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'       => 'employee',
            'status_since' => now()->subMonths(3)->format('Y-m-d'),
            'cv'           => $fake,
            'consent_data' => '1',
        ])
        ->assertSessionHasErrors('cv');
});

it('rejects oversized CV', function () {
    $user = User::factory()->create();
    $big  = UploadedFile::fake()->create('cv.pdf', 6000, 'application/pdf'); // > 5 MB

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'       => 'employee',
            'status_since' => now()->subMonths(3)->format('Y-m-d'),
            'cv'           => $big,
            'consent_data' => '1',
        ])
        ->assertSessionHasErrors('cv');
});

it('stores the CV in the local (non-public) disk', function () {
    $user = User::factory()->create();
    $cv   = secUploadFakePdf();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'status'        => 'employee',
            'status_since'  => now()->subMonths(6)->format('Y-m-d'),
            'problematique' => 'Faire évoluer ma carrière.',
            'cv'            => $cv,
            'consent_data'  => '1',
        ]);

    $profile = $user->fresh()->profile;
    expect($profile->cv_path)->not->toBeNull();

    // Le chemin doit être dans le dossier privé de l'utilisateur
    expect($profile->cv_path)->toContain("cvs/{$user->id}");

    // Le fichier doit exister sur le disque local
    Storage::disk('local')->assertExists($profile->cv_path);

    // Le fichier ne doit PAS être accessible publiquement
    Storage::disk('public')->assertMissing($profile->cv_path);
});

// ─── Isolation entre utilisateurs ────────────────────────────────────────────

it('prevents user A from accessing profile of user B', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    \App\Models\Profile::factory()->for($userB)->cvUploaded()->create();

    // UserA ne doit pas pouvoir afficher la page d'onboarding de B
    // (la page affiche le profil du user connecté uniquement)
    $this->actingAs($userA)
        ->get(route('onboarding.show'))
        ->assertInertia(fn ($page) => $page->where('profile', null)); // profil A absent
});

it('prevents a user from overwriting another user CV path via tampering', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    \App\Models\Profile::factory()->for($userB)->cvUploaded()->create();

    $cv = secUploadFakePdf();

    // UserA soumet l'onboarding : le CV doit être stocké dans cvs/{userA->id}
    $this->actingAs($userA)
        ->post(route('onboarding.store'), [
            'status'        => 'employee',
            'status_since'  => now()->subMonths(3)->format('Y-m-d'),
            'problematique' => 'Sécuriser mon parcours professionnel.',
            'cv'            => $cv,
            'consent_data'  => '1',
        ]);

    $profileA = $userA->fresh()->profile;
    expect($profileA->cv_path)->toStartWith("cvs/{$userA->id}");

    // Le profil de B doit être intact
    $profileB = $userB->fresh()->profile;
    expect($profileB->cv_path)->not->toBe($profileA->cv_path);
});

// ─── Unauthenticated ─────────────────────────────────────────────────────────

it('blocks guest from uploading a CV', function () {
    $this->post(route('onboarding.store'), [
        'status'       => 'employee',
        'status_since' => now()->subMonths(3)->format('Y-m-d'),
        'cv'           => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        'consent_data' => '1',
    ])->assertRedirect(route('login'));
});
