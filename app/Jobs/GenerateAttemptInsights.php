<?php

namespace App\Jobs;

use App\Models\TestAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\AI\Services\JobSuggestionService;
use Praxis\Core\AI\Services\ProfileSynthesisService;
use Praxis\Core\Plugins\PluginHooks;

// #7 — ShouldBeUnique empêche les doublons de jobs IA (double-clic, double-submit).
// uniqueId() scopé par attemptId : une seule instance en file à la fois par tentative.
class GenerateAttemptInsights implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 240;

    public function __construct(public int $attemptId) {}

    /** Clé d'unicité : un seul job par tentative en attente/exécution. */
    public function uniqueId(): string
    {
        return "attempt_{$this->attemptId}";
    }

    public function handle(ProfileSynthesisService $synthesis, JobSuggestionService $jobs): void
    {
        $attempt = TestAttempt::with(['user.profile', 'test', 'result'])->findOrFail($this->attemptId);

        // Guard DB : si la synthèse existe déjà, on ne rappelle pas OpenAI.
        // Protège les queues sync (OVH) où ShouldBeUnique ne bloque plus après exécution.
        if ($attempt->result?->ai_synthesis) {
            logger()->info("GenerateAttemptInsights: synthèse déjà présente pour attempt #{$this->attemptId}, skip.");
            return;
        }

        $synthesis->synthesize($attempt);
        $jobs->suggest($attempt);

        PluginHooks::doAction('insights.generated', $attempt->fresh('result'));
    }
}
