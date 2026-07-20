<?php

use App\Jobs\GenerateGlobalGrimoire;
use App\Models\Profile;
use App\Models\ProfileGrimoire;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\Contracts\AIDriverContract;
use Praxis\Core\AI\Services\GlobalGrimoireService;

// ─── Helpers (noms préfixés grim* pour éviter les collisions avec les autres suites) ──

function grimUser(): User
{
    $user = User::factory()->create();
    Profile::factory()->for($user)->cvUploaded()->create();
    return $user;
}

function grimCompletedAttempt(User $user, string $name = 'Test', ?string $synthesis = 'Synthèse du test.'): TestAttempt
{
    $test = Test::create([
        'slug'              => 'grim-' . uniqid(),
        'name'              => $name,
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);

    $attempt = TestAttempt::create([
        'user_id'          => $user->id,
        'test_id'          => $test->id,
        'status'           => 'completed',
        'started_at'       => now()->subMinutes(10),
        'completed_at'     => now(),
        'last_activity_at' => now(),
        'progress'         => [],
    ]);

    TestResult::create([
        'attempt_id'   => $attempt->id,
        'scoring'      => ['dimensions' => ['a' => 70]],
        'ai_synthesis' => $synthesis,
        'generated_at' => now(),
    ]);

    return $attempt->fresh('result');
}

/** Faux driver IA renvoyant un JSON canné (pas d'appel réseau). */
function grimFakeDriver(?string $json = null): AIDriverContract
{
    $json ??= json_encode([
        'synthese' => "Premier paragraphe transversal.\nDeuxième paragraphe.",
        'voies'    => [
            ['titre' => 'Coach', 'secteur' => 'RH', 'fit_score' => 88, 'pourquoi' => 'Alignement fort.', 'appui_tests' => ['Test A'], 'prochaine_etape' => 'Se renseigner.'],
            ['titre' => 'Analyste', 'secteur' => 'Data', 'fit_score' => 72, 'pourquoi' => 'Curiosité analytique.', 'appui_tests' => ['Test B'], 'prochaine_etape' => 'Suivre une formation.'],
        ],
    ], JSON_UNESCAPED_UNICODE);

    return new class($json) implements AIDriverContract {
        public function __construct(private string $json) {}
        public function key(): string { return 'fake'; }
        public function model(): string { return 'fake-model'; }
        public function generate(string $prompt, array $options = []): string { return $this->json; }
        public function chat(array $messages, array $options = []): string { return $this->json; }
        public function chatMany(array $batch, array $options = []): array { return array_map(fn () => $this->json, $batch); }
        public function generateJson(string $prompt, array $schema = [], array $options = []): array { return json_decode($this->json, true); }
        public function lastUsage(): array { return ['input_tokens' => 10, 'output_tokens' => 20]; }
    };
}

/** Remplace l'AIManager du conteneur pour qu'il renvoie $driver. */
function grimBindDriver(AIDriverContract $driver): void
{
    $mgr = Mockery::mock(AIManager::class);
    $mgr->shouldReceive('forTask')->andReturn($driver);
    app()->instance(AIManager::class, $mgr);
}

// ─── Accès & état vide ──────────────────────────────────────────────────────────

it('redirects guests away from the grimoire', function () {
    $this->get(route('grimoire.show'))->assertRedirect(route('login'));
});

it('shows an empty grimoire when the user has no completed test', function () {
    $user = grimUser();

    $this->actingAs($user)
        ->get(route('grimoire.show'))
        ->assertInertia(fn ($page) => $page
            ->component('Candidate/Grimoire')
            ->where('is_empty', true)
        );
});

// ─── État prêt + anti-régénération ───────────────────────────────────────────────

it('shows a ready grimoire without re-dispatching when the signature is current', function () {
    Queue::fake();
    $user = grimUser();
    grimCompletedAttempt($user, 'Test A');
    grimCompletedAttempt($user, 'Test B');

    $service = app(GlobalGrimoireService::class);
    $signature = $service->signature($service->completedAttempts($user));

    ProfileGrimoire::create([
        'user_id'         => $user->id,
        'synthesis'       => 'Une relecture.',
        'voies'           => [['titre' => 'Coach']],
        'tests_signature' => $signature,
        'status'          => 'ready',
        'generated_at'    => now(),
    ]);

    $this->actingAs($user)
        ->get(route('grimoire.show'))
        ->assertInertia(fn ($page) => $page
            ->component('Candidate/Grimoire')
            ->where('ai_pending', false)
            ->where('grimoire.status', 'ready')
        );

    Queue::assertNotPushed(GenerateGlobalGrimoire::class);
});

it('re-dispatches generation when a new test makes the grimoire stale', function () {
    Queue::fake();
    $user = grimUser();
    grimCompletedAttempt($user, 'Test A');

    ProfileGrimoire::create([
        'user_id'         => $user->id,
        'synthesis'       => 'Ancienne relecture.',
        'voies'           => [['titre' => 'Ancienne piste']],
        'tests_signature' => 'signature-perimee',
        'status'          => 'ready',
        'generated_at'    => now()->subDay(),
    ]);

    // Génération progressive : on garde l'ancienne synthèse à l'écran (donc pas
    // d'écran d'attente pleine page : ai_pending=false), mais les pistes sont
    // marquées "en cours" (voies_pending=true) et le job est (re)dispatché.
    $this->actingAs($user)
        ->get(route('grimoire.show'))
        ->assertInertia(fn ($page) => $page
            ->where('ai_pending', false)
            ->where('voies_pending', true)
        );

    Queue::assertPushed(GenerateGlobalGrimoire::class);
});

// ─── Génération réelle (faux driver) ─────────────────────────────────────────────

it('generates a global grimoire crossing all tests', function () {
    $user = grimUser();
    grimCompletedAttempt($user, 'Test A');
    grimCompletedAttempt($user, 'Test B');

    grimBindDriver(grimFakeDriver());

    $grimoire = app(GlobalGrimoireService::class)->generate($user);

    expect($grimoire)->not->toBeNull();
    expect($grimoire->status)->toBe('ready');
    expect($grimoire->synthesis)->toContain('transversal');
    expect($grimoire->voies)->toHaveCount(2);
    expect($grimoire->ai_metadata['tests_count'])->toBe(2);
});

// ─── Fallback en cas d'échec IA ──────────────────────────────────────────────────

it('writes a failed status when the AI throws', function () {
    $user = grimUser();
    grimCompletedAttempt($user, 'Test A');

    $thrower = new class implements AIDriverContract {
        public function key(): string { return 'boom'; }
        public function model(): string { return 'boom'; }
        public function generate(string $prompt, array $options = []): string { throw new RuntimeException('AI down'); }
        public function chat(array $messages, array $options = []): string { throw new RuntimeException('AI down'); }
        public function chatMany(array $batch, array $options = []): array { throw new RuntimeException('AI down'); }
        public function generateJson(string $prompt, array $schema = [], array $options = []): array { throw new RuntimeException('AI down'); }
        public function lastUsage(): array { return []; }
    };
    grimBindDriver($thrower);

    (new GenerateGlobalGrimoire($user->id, force: true))->handle(app(GlobalGrimoireService::class));

    $grimoire = $user->fresh()->profileGrimoire;
    expect($grimoire->status)->toBe('failed');
    expect($grimoire->synthesis)->not->toBeNull();
});

// ─── PDF ─────────────────────────────────────────────────────────────────────────

it('refuses the PDF when the grimoire is not ready', function () {
    $user = grimUser();
    grimCompletedAttempt($user, 'Test A');

    $this->actingAs($user)
        ->get(route('grimoire.pdf'))
        ->assertNotFound();
});
