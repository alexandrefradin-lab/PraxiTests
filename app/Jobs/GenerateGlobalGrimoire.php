<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\AI\Services\GlobalGrimoireService;

/**
 * (Re)génère le Grimoire global d'un candidat : relecture transversale de tous
 * ses tests. Déclenché automatiquement après chaque test terminé
 * (depuis GenerateAttemptInsights) et manuellement via le bouton "Régénérer".
 *
 * ShouldBeUnique scopé par user → pas de doublon de job en file.
 */
class GenerateGlobalGrimoire implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 240;

    /** Backoff entre tentatives (cf. audit T-3) : on espace les retries IA. */
    public function backoff(): array
    {
        return [15, 45];
    }

    public function __construct(public int $userId, public bool $force = false) {}

    public function uniqueId(): string
    {
        return "grimoire_user_{$this->userId}";
    }

    /**
     * Durée de vie du verrou d'unicité (secondes).
     *
     * En QUEUE_CONNECTION=sync + afterResponse() sur OVH, le job tourne dans le
     * process PHP après la réponse : si max_execution_time tue le process en plein
     * appel IA, le verrou ShouldBeUnique n'est jamais relâché. Avec la valeur par
     * défaut (3600s) plus aucun re-dispatch n'aboutit pendant 1h → le Grimoire
     * reste figé sur "en cours de relecture". On borne donc l'auto-réparation à
     * 5 min : la prochaine visite de /grimoire pourra relancer la génération.
     */
    public function uniqueFor(): int
    {
        return 300;
    }

    public function handle(GlobalGrimoireService $service): void
    {
        // Le job s'exécute en mode sync (afterResponse) sur OVH : le $timeout=240
        // du worker est alors IGNORÉ, c'est max_execution_time de PHP qui s'applique
        // et tue le process en plein appel IA (voies = jusqu'à 8000 tokens, ~1-2 min).
        // On neutralise donc la limite pour ce traitement de fond détaché.
        @set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }

        $user = User::with('profile')->find($this->userId);
        if (!$user) {
            return;
        }

        $attempts = $service->completedAttempts($user);
        if ($attempts->isEmpty()) {
            return; // rien à relire
        }

        // Anti-régénération : si la composition des tests n'a pas changé, on ne
        // rappelle pas l'IA. Le bouton "Régénérer" passe force=true pour outrepasser.
        $grimoire  = $user->grimoire();
        $signature = $service->signature($attempts);
        if (!$this->force && $grimoire->status === 'ready' && $grimoire->tests_signature === $signature) {
            logger()->info("GenerateGlobalGrimoire: signature inchangée pour user #{$this->userId}, skip.");
            return;
        }

        // Idempotence des retries (cf. audit T-4) : si une tentative précédente de
        // CE job a déjà régénéré le Grimoire avec succès (status ready + même
        // signature + généré il y a moins de 2 min), on NE relance PAS les appels
        // IA — même en mode force. Sans ça, un échec post-génération (ex. update DB)
        // déclenchait une 2e relecture complète payante (~10 600 tokens).
        if ($grimoire->status === 'ready'
            && $grimoire->tests_signature === $signature
            && $grimoire->generated_at
            && $grimoire->generated_at->gt(now()->subMinutes(2))) {
            logger()->info("GenerateGlobalGrimoire: déjà régénéré récemment pour user #{$this->userId}, skip (idempotence).");
            return;
        }

        try {
            $service->generate($user);
        } catch (\Throwable $e) {
            logger()->error("GenerateGlobalGrimoire: échec IA pour user #{$this->userId}: {$e->getMessage()}");
            $this->writeFallback($user);
        }
    }

    /**
     * En cas d'échec IA, ne jamais laisser le candidat sur un écran de chargement.
     * On écrit un état "failed" + message de repli si aucune synthèse n'existe encore.
     */
    protected function writeFallback(User $user): void
    {
        $grimoire = $user->grimoire();

        if ($grimoire->status === 'ready' && $grimoire->synthesis) {
            return; // on garde la version précédente valable
        }

        $grimoire->update([
            'status'       => 'failed',
            'synthesis'    => $grimoire->synthesis
                ?: "La relecture globale n'a pas pu être générée pour le moment. "
                . "Tes synthèses par test restent disponibles. Tu peux réessayer plus tard "
                . "ou en parler avec ton conseiller.",
            'generated_at' => now(),
        ]);
    }

    /**
     * Filet de sécurité du worker : appelé quand le job échoue définitivement
     * (toutes les tentatives épuisées). Garantit que le statut ne reste jamais
     * coincé sur "pending" → le front arrête de tourner et affiche le repli.
     */
    public function failed(\Throwable $e): void
    {
        logger()->error("GenerateGlobalGrimoire: échec définitif pour user #{$this->userId}: {$e->getMessage()}");

        $user = User::with('profile')->find($this->userId);
        if ($user) {
            $this->writeFallback($user);
        }
    }
}
