<?php

namespace Praxis\Plugins\PraxiVision\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Praxis\Plugins\PraxiVision\Models\VisionJourney;
use Praxis\Plugins\PraxiVision\Models\VisionPracticeProgress;

class LeadershipJourneyService
{
    public const TOTAL_DAYS = 60;
    public const ECLATS_PER_PRACTICE = 20;

    public function journeyFor(User $user): VisionJourney
    {
        return VisionJourney::firstOrCreate(
            ['user_id' => $user->id],
            ['started_on' => Carbon::today()],
        );
    }

    public function currentDay(VisionJourney $journey): int
    {
        $elapsed = $journey->started_on->startOfDay()->diffInDays(Carbon::today(), false);
        $day     = $elapsed + 1;

        return max(1, min(self::TOTAL_DAYS, $day));
    }

    public function isUnlocked(VisionJourney $journey, int $dayIndex): bool
    {
        return $dayIndex >= 1
            && $dayIndex <= self::TOTAL_DAYS
            && $dayIndex <= $this->currentDay($journey);
    }

    public function daysUntilUnlock(VisionJourney $journey, int $dayIndex): int
    {
        return max(0, $dayIndex - $this->currentDay($journey));
    }

    public function streakFor(User $user): int
    {
        if (! Schema::hasTable('vision_practice_progress')) {
            return 0;
        }

        $days = VisionPracticeProgress::forUser($user->id)
            ->whereNotNull('completed_at')
            ->pluck('completed_at')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->sort()
            ->values();

        if ($days->isEmpty()) {
            return 0;
        }

        $today = Carbon::today();
        $last  = Carbon::parse($days->last());

        if ($last->diffInDays($today) > 1) {
            return 0;
        }

        $streak = 1;
        for ($i = $days->count() - 1; $i > 0; $i--) {
            $cur  = Carbon::parse($days[$i]);
            $prev = Carbon::parse($days[$i - 1]);
            if ($prev->diffInDays($cur) === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
