<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAttemptInsights;
use App\Models\TestResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Relance automatique des synthèses IA bloquées.
 *
 * Sur OVH (sync + afterResponse), le process PHP peut être tué par
 * max_execution_time avant que la synthèse ne soit écrite. Deux états
 * de blocage sont couverts :
 *
 *  - Zombie    : ai_synthesis=null, ai_failed=false, completed_at > 5 min
 *                → process tué avant le catch/shutdown
 *  - Échoué    : ai_failed=true (shutdown ou catch ont écrit le fallback)
 *                → l'IA a planté (clé API, quota, erreur réseau…)
 *
 * Les jobs sont dispatché sur la connexion "database" : le worker OVH
 * (queue:work database, schedulé toutes les minutes) les traite en fond,
 * indépendamment du cycle HTTP. Pas besoin de changer QUEUE_CONNECTION.
 *
 * Planification recommandée : toutes les 5 minutes (voir routes/console.php).
 * Cooldown par tentative (Cache 10 min) pour éviter les relances en rafale.
 */
class RetryZombieInsights extends Command
{
    protected $signature = 'insights:retry-zombies
                            {--dry-run : Affiche les cas détectés sans les relancer}
                            {--older-than=5 : Âge minimum en minutes pour les zombies}';

    protected $description = 'Relance automatiquement les synthèses IA bloquées (zombies + échecs)';

    public function handle(): int
    {
        $olderThan = (int) $this->option('older-than');
        $dryRun    = $this->option('dry-run');
        $cutoff    = now()->subMinutes($olderThan);

        // ── 1. Zombies ────────────────────────────────────────────────────────
        // Passation terminée, sans synthèse ni flag d'échec = process PHP tué.
        $zombieIds = TestResult::whereNull('ai_synthesis')
            ->where('ai_failed', false)
            ->whereHas('attempt', fn ($q) => $q
                ->where('status', 'completed')
                ->where('completed_at', '<', $cutoff)
            )
            ->pluck('attempt_id')
            ->filter()
            ->values();

        // ── 2. Échecs marqués ────────────────────────────────────────────────
        // ai_failed=true = fallback écrit mais synthèse réelle jamais générée.
        $failedIds = TestResult::where('ai_failed', true)
            ->whereHas('attempt', fn ($q) => $q->where('status', 'completed'))
            ->pluck('attempt_id')
            ->filter()
            ->values();

        $allIds = $zombieIds->merge($failedIds)->unique()->values();

        if ($allIds->isEmpty()) {
            $this->info('Aucune synthèse à relancer.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Détectés → %d zombie(s), %d échec(s) = %d à relancer.',
            $zombieIds->count(),
            $failedIds->count(),
            $allIds->count(),
        ));

        if ($dryRun) {
            $this->table(['attempt_id', 'type'], $allIds->map(fn ($id) => [
                $id,
                $zombieIds->contains($id) ? 'zombie' : 'échec',
            ])->toArray());
            return self::SUCCESS;
        }

        // Réinitialiser les flags ai_failed pour que le polling candidat reprenne.
        TestResult::whereIn('attempt_id', $failedIds)->update([
            'ai_synthesis' => null,
            'ai_failed'    => false,
            'ai_error'     => null,
            'generated_at' => null,
        ]);

        $dispatched = 0;
        foreach ($allIds as $attemptId) {
            // Cooldown : évite de relancer un même attempt toutes les 5 min
            // si l'IA est durablement indisponible (quota épuisé, etc.).
            $cooldownKey = "insights_auto_retry_{$attemptId}";
            if (! Cache::add($cooldownKey, 1, now()->addMinutes(10))) {
                $this->line("  skip attempt #{$attemptId} (cooldown actif)");
                continue;
            }

            // Purge le verrou ShouldBeUnique résiduel (job précédent tué).
            Cache::forget(
                'laravel_unique_job:' . GenerateAttemptInsights::class . ':attempt_' . $attemptId
            );

            // Dispatch sur la connexion database : le worker OVH (queue:work database)
            // le récupère au prochain passage (toutes les minutes).
            GenerateAttemptInsights::dispatch($attemptId)
                ->onConnection('database')
                ->onQueue('default');

            $this->line("  → attempt #{$attemptId} envoyé en file");
            $dispatched++;
        }

        $this->info("Relancé : {$dispatched} job(s).");

        logger()->info("insights:retry-zombies — {$dispatched} relancé(s)", [
            'zombies' => $zombieIds->count(),
            'failed'  => $failedIds->count(),
            'dispatched' => $dispatched,
        ]);

        return self::SUCCESS;
    }
}
