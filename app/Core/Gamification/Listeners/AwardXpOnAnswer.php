<?php

namespace Praxis\Core\Gamification\Listeners;

use App\Models\TestAttempt;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Plugins\PluginHooks;

class AwardXpOnAnswer
{
    public static function register(GamificationEngine $engine): void
    {
        PluginHooks::action('attempt.answered', function (TestAttempt $attempt) use ($engine) {
            if (!$attempt->user) return;
            $attempt->loadMissing(['test', 'user']); // Évite N+1 (ARC-M6)
            $engine->awardXp(
                $attempt->user,
                config('gamification.xp.answer_question', 10),
                'answer_question',
                $attempt->test,
                ['attempt_id' => $attempt->id],
                evaluateBadges: false, // hot path : badges évalués à attempt.completed
            );
        });

        PluginHooks::action('attempt.completed', function (TestAttempt $attempt) use ($engine) {
            if (!$attempt->user) return;
            $attempt->loadMissing(['test', 'user']); // Évite N+1 (ARC-M6)

            // Anti-doublon : vérifie qu'aucun XP de complétion n'a déjà été octroyé
            // pour cette tentative (ex. retry du listener ou double-fire d'event).
            $alreadyAwarded = \DB::table('xp_events')
                ->where('user_id', $attempt->user->id)
                ->where('reason', 'complete_test')
                ->whereJsonContains('context->attempt_id', $attempt->id)
                ->exists();

            if ($alreadyAwarded) {
                return;
            }

            $engine->awardXp(
                $attempt->user,
                config('gamification.xp.complete_test', 200),
                'complete_test',
                $attempt->test,
                ['attempt_id' => $attempt->id],
            );
        });
    }
}
