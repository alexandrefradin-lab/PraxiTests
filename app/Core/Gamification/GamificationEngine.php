<?php

namespace Praxis\Core\Gamification;

use App\Models\Badge;
use App\Models\GamificationProgress;
use App\Models\Test;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Praxis\Core\Plugins\PluginHooks;

class GamificationEngine
{
    public function __construct(protected BadgeEvaluator $evaluator) {}

    /**
     * @param  bool  $evaluateBadges  Évaluer les badges après l'octroi ? Mettre à false
     *   sur le hot path (chaque réponse) : aucun badge n'est satisfiable mi-épreuve, et
     *   l'évaluation (Badge::all() + requêtes par badge) coûte cher à chaque clic. Les
     *   badges restent évalués à la fin de l'épreuve et aux déblocages d'insight.
     */
    public function awardXp(User $user, int $amount, string $reason, ?Test $test = null, array $context = [], bool $evaluateBadges = true, ?string $idempotencyKey = null): GamificationProgress
    {
        // Idempotency guard (MET-M5) : si une clé est fournie et déjà présente, ne rien faire.
        if ($idempotencyKey !== null) {
            if (\DB::table('xp_events')->where('idempotency_key', $idempotencyKey)->exists()) {
                return GamificationProgress::where('user_id', $user->id)
                    ->where('test_id', $test?->id)
                    ->firstOrFail();
            }
        }

        // S'assurer que la ligne existe — protégé contre les race conditions par transaction
        DB::transaction(function () use ($user, $test, $amount) {
            try {
                GamificationProgress::firstOrCreate(
                    ['user_id' => $user->id, 'test_id' => $test?->id],
                    ['xp_total' => 0, 'level' => 1]
                );
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Insertion concurrente : la ligne existe déjà, on continue.
            }

            // Incrément atomique
            $query = GamificationProgress::where('user_id', $user->id)
                ->where('test_id', $test?->id);
            if ($amount >= 0) {
                $query->increment('xp_total', $amount);
            } else {
                $query->decrement('xp_total', abs($amount));
            }
        });

        // Recharger pour avoir la valeur à jour et recalculer le level
        $progress = GamificationProgress::where('user_id', $user->id)
            ->where('test_id', $test?->id)
            ->firstOrFail();
        $newLevel = $this->levelFromXp($progress->xp_total);
        if ($newLevel !== $progress->level) {
            $progress->update(['level' => $newLevel]);
            $progress->refresh();
        }

        \DB::table('xp_events')->insert([
            'user_id' => $user->id,
            'reason'  => $reason,
            'xp'      => $amount,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Invalider le cache Éclats partagé avec HandleInertiaRequests (ARC-m2).
        \Illuminate\Support\Facades\Cache::forget("eclats.{$user->id}");

        PluginHooks::doAction('gamification.xp_awarded', $user, $amount, $reason, $progress);
        if ($evaluateBadges) {
            $this->evaluator->evaluate($user, ['type' => 'xp_awarded', 'amount' => $amount, 'reason' => $reason]);
        }

        return $progress;
    }

    public function levelFromXp(int $xp): int
    {
        $levels = config('gamification.levels') ?? [];
        $current = 1;
        foreach ($levels as $level) {
            if ($xp >= $level['xp_required']) {
                $current = $level['level'];
            }
        }
        return $current;
    }

    public function levelInfo(int $level): array
    {
        $levels = collect(config('gamification.levels'));
        return $levels->firstWhere('level', $level) ?? $levels->first();
    }

    public function nextLevelInfo(int $level): ?array
    {
        $levels = collect(config('gamification.levels'));
        return $levels->firstWhere('level', $level + 1);
    }

    /**
     * Total d'Éclats cumulés par l'utilisateur, tous tests confondus.
     * Sert de base aux paliers de déblocage (ex. plugin praxiboost) et à
     * la barre d'Éclats globale affichée dans le layout candidat.
     */
    public function totalEclats(User $user): int
    {
        return (int) GamificationProgress::where('user_id', $user->id)->sum('xp_total');
    }

    /**
     * Progression globale (toutes parties confondues) : total d'Éclats,
     * niveau dérivé du total et avancement vers le niveau suivant.
     */
    public function globalProgressOf(User $user): array
    {
        $total = $this->totalEclats($user);
        $level = $this->levelFromXp($total);
        $current = $this->levelInfo($level);
        $next = $this->nextLevelInfo($level);

        $towardsNext = $next
            ? max(0, min(100, round((($total - $current['xp_required']) / max(1, $next['xp_required'] - $current['xp_required'])) * 100, 1)))
            : 100;

        return [
            'xp_total'        => $total,
            'xp_progress'     => $towardsNext,
            'level'           => $level,
            'level_name'      => $current['name'] ?? null,
            'next_level'      => $next['level'] ?? null,
            'next_level_name' => $next['name'] ?? null,
            'next_level_xp'   => $next['xp_required'] ?? null,
        ];
    }

    public function progressOf(User $user, ?Test $test = null): array
    {
        $progress = GamificationProgress::firstOrCreate(
            ['user_id' => $user->id, 'test_id' => $test?->id],
            ['xp_total' => 0, 'level' => 1]
        );

        $current = $this->levelInfo($progress->level);
        $next = $this->nextLevelInfo($progress->level);

        $towardsNext = $next
            ? max(0, min(100, round((($progress->xp_total - $current['xp_required']) / max(1, $next['xp_required'] - $current['xp_required'])) * 100, 1)))
            : 100;

        return [
            'xp'                => $progress->xp_total,
            'level'             => $progress->level,
            'level_name'        => $current['name'] ?? null,
            'next_level'        => $next['level'] ?? null,
            'next_level_name'   => $next['name'] ?? null,
            'next_level_xp'     => $next['xp_required'] ?? null,
            'percent_to_next'   => $towardsNext,
            'badges_count'      => $user->badges()->count(),
            'insights_unlocked' => $progress->insights_unlocked ?? [],
        ];
    }

    public function unlockInsight(User $user, Test $test, string $insightKey, mixed $payload = null): void
    {
        $progress = GamificationProgress::firstOrCreate(
            ['user_id' => $user->id, 'test_id' => $test->id],
            ['xp_total' => 0, 'level' => 1]
        );

        $insights = $progress->insights_unlocked ?? [];
        if (!isset($insights[$insightKey])) {
            $insights[$insightKey] = ['unlocked_at' => now()->toIso8601String(), 'payload' => $payload];
            $progress->update(['insights_unlocked' => $insights]);
            $this->awardXp($user, config('gamification.xp.unlock_insight', 25), "insight:{$insightKey}", $test);
            PluginHooks::doAction('gamification.insight_unlocked', $user, $test, $insightKey, $payload);
        }
    }
}
