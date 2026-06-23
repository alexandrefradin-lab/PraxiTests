<?php

namespace Praxis\Core\Mailing\Listeners;

use App\Models\TestAttempt;
use Praxis\Core\Mailing\Services\SequenceRunner;
use Praxis\Core\Plugins\PluginHooks;

/**
 * Câble les séquences email aux événements du cycle de vie (cf. audit Fo-4).
 *
 * Le moteur SequenceRunner::trigger() existait mais n'était jamais appelé.
 * On l'accroche ici aux hooks `attempt.completed` / `attempt.started`.
 *
 * trigger() ne fait rien s'il n'existe aucune séquence ACTIVE pour l'événement,
 * donc le branchement est sans effet tant qu'un admin n'a pas configuré de
 * séquence. Toute erreur d'envoi est isolée pour ne jamais casser la passation.
 *
 * NB (OVH / QUEUE_CONNECTION=sync) : les délais entre étapes (delay_hours)
 * reposent sur la file. En mode `sync`, delay() est ignoré et toutes les étapes
 * partiraient d'affilée — passer QUEUE_CONNECTION=database + cron schedule:run
 * pour des relances réellement échelonnées.
 */
class TriggerEmailSequences
{
    public static function register(SequenceRunner $runner): void
    {
        PluginHooks::action('attempt.completed', function (TestAttempt $attempt) use ($runner) {
            self::safeTrigger($runner, 'attempt_completed', $attempt);
        });

        PluginHooks::action('attempt.started', function (TestAttempt $attempt) use ($runner) {
            self::safeTrigger($runner, 'attempt_started', $attempt);
        });
    }

    protected static function safeTrigger(SequenceRunner $runner, string $event, TestAttempt $attempt): void
    {
        if (! $attempt->user) {
            return;
        }

        try {
            $runner->trigger($event, $attempt->user, [
                'test_id'    => $attempt->test_id,
                'attempt_id' => $attempt->id,
            ]);
        } catch (\Throwable $e) {
            // Le mailing ne doit jamais interrompre le parcours du candidat.
            logger()->warning('TriggerEmailSequences: ' . $e::class);
        }
    }
}
