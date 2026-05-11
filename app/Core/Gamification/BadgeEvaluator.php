<?php

namespace Praxis\Core\Gamification;

use App\Models\Badge;
use App\Models\User;
use Praxis\Core\Plugins\PluginHooks;

class BadgeEvaluator
{
    /**
     * Évalue tous les badges et attribue ceux dont les critères sont remplis.
     * Critères supportés (extensibles via filter `gamification.badge.criterion`) :
     *   { "type": "xp_total", "min": 200 }
     *   { "type": "tests_completed", "min": 1 }
     *   { "type": "fast_completion", "test_id": X, "max_seconds": 600 }
     *   { "type": "section_perfect", "section_id": X }
     */
    public function evaluate(User $user, array $event): void
    {
        $badges = Badge::all();
        foreach ($badges as $badge) {
            if ($user->badges()->where('badges.id', $badge->id)->exists()) {
                continue;
            }
            if ($this->meetsCriteria($user, $badge->criteria, $event)) {
                $this->award($user, $badge);
            }
        }
    }

    public function meetsCriteria(User $user, ?array $criteria, array $event): bool
    {
        if (!$criteria) return false;

        $type = $criteria['type'] ?? null;

        $result = match ($type) {
            'xp_total'         => $user->totalXp() >= ($criteria['min'] ?? 0),
            'tests_completed'  => $user->attempts()->where('status', 'completed')->count() >= ($criteria['min'] ?? 1),
            'first_test'       => $user->attempts()->where('status', 'completed')->count() === 1,
            'cv_uploaded'      => (bool) ($user->profile?->cv_path),
            'fast_completion'  => $this->fastCompletion($user, $criteria),
            'all_questions_answered' => $event['type'] === 'attempt_completed',
            default            => null,
        };

        if ($result === null) {
            $result = (bool) PluginHooks::applyFilters('gamification.badge.criterion', false, $user, $criteria, $event);
        }

        return (bool) $result;
    }

    protected function fastCompletion(User $user, array $criteria): bool
    {
        $q = $user->attempts()->where('status', 'completed');
        if (!empty($criteria['test_id'])) $q->where('test_id', $criteria['test_id']);
        $max = $criteria['max_seconds'] ?? 600;
        return $q->where('time_spent_seconds', '<=', $max)->exists();
    }

    public function award(User $user, Badge $badge, array $context = []): void
    {
        $user->badges()->attach($badge->id, [
            'earned_at' => now(),
            'context'   => json_encode($context, JSON_UNESCAPED_UNICODE),
        ]);

        if ($badge->xp_reward > 0) {
            app(GamificationEngine::class)->awardXp($user, $badge->xp_reward, "badge:{$badge->slug}");
        }

        PluginHooks::doAction('gamification.badge_earned', $user, $badge);
    }
}
