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

    public function __construct(public int $userId, public bool $force = false) {}

    public function uniqueId(): string
    {
        return "grimoire_user_{$this->userId}";
    }

    public function handle(GlobalGrimoireService $service): void
    {
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
}
