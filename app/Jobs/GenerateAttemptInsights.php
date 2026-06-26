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

    /**
     * Auto-réparation du verrou d'unicité : en sync/afterResponse sur OVH, si le
     * process PHP est tué (max_execution_time) en plein appel IA, le verrou n'est
     * pas relâché. On le borne à 5 min au lieu de l'heure par défaut pour qu'un
     * re-déclenchement reste possible rapidement.
     */
    public function uniqueFor(): int
    {
        return 300;
    }

    public function handle(ProfileSynthesisService $synthesis, JobSuggestionService $jobs): void
    {
        // En sync (afterResponse, OVH), $timeout est ignoré : c'est max_execution_time
        // de PHP qui s'applique et peut tuer le process en plein appel IA. On neutralise
        // la limite pour ce traitement de fond détaché.
        @set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }

        $attempt = TestAttempt::with(['user.profile', 'test', 'result'])->findOrFail($this->attemptId);

        // Guard DB : si la synthèse existe déjà, on ne rappelle pas OpenAI.
        // Protège les queues sync (OVH) où ShouldBeUnique ne bloque plus après exécution.
        if ($attempt->result?->ai_synthesis) {
            logger()->info("GenerateAttemptInsights: synthèse déjà présente pour attempt #{$this->attemptId}, skip.");
            return;
        }

        try {
            $synthesis->synthesize($attempt);
            $jobs->suggest($attempt);
            PluginHooks::doAction('insights.generated', $attempt->fresh('result'));

            // ÉCONOMIE IA : on NE régénère PLUS le Grimoire global ici. Avant, chaque
            // test terminé relançait une génération complète (synthèse + N voies) ; sur
            // un parcours de 12 tests = 12 Grimoires générés alors qu'un seul sert.
            // Désormais le Grimoire se (re)génère en LAZY à l'ouverture de /grimoire
            // (GrimoireController::show détecte la signature périmée et dispatch lui-même).
            // → un seul appel IA, au moment où l'utilisateur le consulte réellement.
        } catch (\Throwable $e) {
            // Une panne IA (clé absente, HTTP 4xx/5xx, JSON invalide, timeout) ne doit
            // PAS laisser le candidat sur un écran de chargement infini (ai_pending).
            // On journalise et on écrit une synthèse de repli pour débloquer la page.
            logger()->error("GenerateAttemptInsights: échec IA pour attempt #{$this->attemptId}: {$e->getMessage()}", [
                'attempt_id' => $this->attemptId,
                'exception'  => $e::class,
                'file'       => $e->getFile() . ':' . $e->getLine(),
            ]);
            $this->writeFallback($attempt, $e->getMessage());
        }
    }

    /**
     * Écrit une synthèse de repli si l'IA a échoué, afin que `ai_pending` passe à false
     * (la page de résultats s'affiche au lieu de tourner indéfiniment).
     * Trace ai_failed=true et l'erreur pour permettre un retry admin.
     * N'écrase jamais une synthèse déjà réussie (ai_failed=false).
     */
    protected function writeFallback(TestAttempt $attempt, string $errorMessage = ''): void
    {
        if (!$attempt->result) {
            return;
        }
        // Ne pas écraser une synthèse réelle déjà présente
        if ($attempt->result->ai_synthesis && !$attempt->result->ai_failed) {
            return;
        }

        $attempt->result->update([
            'ai_synthesis' => "La synthèse n'a pas pu être générée automatiquement pour le moment. "
                . "Tes résultats détaillés restent disponibles ci-dessous. "
                . "Tu peux réessayer plus tard ou en parler avec ton conseiller.",
            'ai_failed'    => true,
            'ai_error'     => mb_substr($errorMessage, 0, 1000),
            'generated_at' => now(),
        ]);
    }
}
