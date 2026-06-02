<?php

namespace Praxis\Core\Gamification;

use App\Models\Badge;
use App\Models\GamificationProgress;
use App\Models\Test;
use App\Models\User;
use Praxis\Core\Plugins\PluginHooks;

class GamificationEngine
{
    public function __construct(protected BadgeEvaluator $evaluator) {}

    public function awardXp(User $user, int $amount, string $reason, ?Test $test = null, array $context = []): GamificationProgress
    {
        // S'assurer que la ligne existe
        GamificationProgress::firstOrCreate(
            ['user_id' => $user->id, 'test_id' => $test?->id],
            ['xp_total' => 0, 'level' => 1]
        );

        // Incrément atomique (pas de race condition)
        GamificationProgress::where('user_id', $user->id)
            ->where('test_id', $test?->id)
            ->when($amount >= 0, fn ($q) => $q->increment('xp_total', $amount))
            ->when($amount < 0, fn ($q) => $q->decrement('xp_total', abs($amount)));

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

        PluginHooks::doAction('gamification.xp_awarded', $user, $amount, $reason, $progress);
        $this->evaluator->evaluate($user, ['type' => 'xp_awarded', 'amount' => $amount, 'reason' => $reason]);

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
