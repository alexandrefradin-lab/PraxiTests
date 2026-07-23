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
        // Le catalogue de badges change rarement → on le cache (TTL court) au lieu
        // de le recharger à chaque évaluation. Invalidé naturellement par le TTL.
        $badges = \Illuminate\Support\Facades\Cache::remember(
            'gamification.badges.all',
            300,
            fn () => Badge::all(),
        );

        // Une seule requête pour les badges déjà gagnés (au lieu d'un exists() par badge).
        $earned = $user->badges()->pluck('badges.id')->all();

        foreach ($badges as $badge) {
            if (in_array($badge->id, $earned, true)) {
                continue;
            }
            if ($this->meetsCriteria($user, $badge->criteria, $event)) {
                $this->award($user, $badge);
                $earned[] = $badge->id; // évite une ré-attribution dans la même passe
            }
        }
    }

    public function meetsCriteria(User $user, ?array $criteria, array $event): bool
    {
        if (!$criteria) return false;

        $type = $criteria['type'] ?? null;

        $result = match ($type) {
            'xp_total'         => app(GamificationEngine::class)->totalEclats($user) >= ($criteria['min'] ?? 0),
            'tests_completed'  => $user->attempts()->where('status', 'completed')->count() >= ($criteria['min'] ?? 1),
            'first_test'       => $user->attempts()->where('status', 'completed')->count() === 1,
            'cv_uploaded'      => (bool) ($user->profile?->cv_path),
            'fast_completion'  => $this->fastCompletion($user, $criteria),
            'all_questions_answered' => $event['type'] === 'attempt_completed',
            'all_badges'       => $this->allOtherBadgesEarned($user),
            default            => null,
        };

        if ($result === null) {
            $result = (bool) PluginHooks::applyFilters('gamification.badge.criterion', false, $user, $criteria, $event);
        }

        return (bool) $result;
    }

    /**
     * Méta-critère : tous les badges SAUF les méta-badges eux-mêmes.
     *
     * L'exclusion est indispensable — un méta-badge qui se compterait dans sa
     * propre cible ne serait jamais atteignable. Elle porte sur le TYPE de
     * critère, pas sur un slug, pour qu'un second méta-badge ne casse pas le
     * premier.
     */
    protected function allOtherBadgesEarned(User $user): bool
    {
        $cibles = Badge::all()
            ->reject(fn (Badge $b) => ($b->criteria['type'] ?? null) === 'all_badges')
            ->pluck('id');

        if ($cibles->isEmpty()) {
            return false;
        }

        $obtenus = $user->badges()->whereIn('badges.id', $cibles)->count();

        return $obtenus >= $cibles->count();
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
        // syncWithoutDetaching : idempotent — un badge déjà possédé n'est ni
        // ré-inséré (violation user_badges unique → 500) ni re-crédité en Éclats.
        try {
            $changes = $user->badges()->syncWithoutDetaching([
                $badge->id => [
                    'earned_at' => now(),
                    'context'   => json_encode($context, JSON_UNESCAPED_UNICODE),
                ],
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return; // attribution concurrente (double clic / requêtes parallèles)
        }

        if (empty($changes['attached'])) {
            return;
        }

        if ($badge->xp_reward > 0) {
            // evaluateBadges: false — on est déjà dans une passe d'évaluation ;
            // la ré-évaluation récursive attribuait les badges suivants avant que
            // la boucle appelante ne les atteigne, d'où des doublons user_badges.
            // Les paliers franchis grâce à ces Éclats sont couverts par la suite
            // de la passe en cours (totalEclats relit la base à chaque critère).
            app(GamificationEngine::class)->awardXp($user, $badge->xp_reward, "badge:{$badge->slug}", evaluateBadges: false);
        }

        PluginHooks::doAction('gamification.badge_earned', $user, $badge);
    }
}
