<?php

namespace Praxis\Plugins\PraxiMiroir\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Praxis\Plugins\PraxiMiroir\Models\MirrorJourney;
use Praxis\Plugins\PraxiMiroir\Models\MirrorProgress;

/**
 * Cadence « un exercice par jour ».
 * Le parcours dure 30 jours. Le jour J se débloque J-1 jours après started_on.
 * Jour 1 disponible immédiatement, jour 2 le lendemain, etc.
 * Les jours passés restent accessibles (rattrapage).
 */
class MirrorJourneyService
{
    public const TOTAL_DAYS          = 30;
    public const ECLATS_PER_EXERCISE = 20;

    public function journeyFor(User $user): MirrorJourney
    {
        return MirrorJourney::firstOrCreate(
            ['user_id' => $user->id],
            ['started_on' => Carbon::today()],
        );
    }

    public function currentDay(MirrorJourney $journey): int
    {
        $elapsed = $journey->started_on->startOfDay()->diffInDays(Carbon::today(), false);
        $day     = $elapsed + 1;

        return max(1, min(self::TOTAL_DAYS, $day));
    }

    public function isUnlocked(MirrorJourney $journey, int $dayIndex): bool
    {
        return $dayIndex >= 1
            && $dayIndex <= self::TOTAL_DAYS
            && $dayIndex <= $this->currentDay($journey);
    }

    public function daysUntilUnlock(MirrorJourney $journey, int $dayIndex): int
    {
        return max(0, $dayIndex - $this->currentDay($journey));
    }

    public function streakFor(User $user): int
    {
        if (! Schema::hasTable('mirror_progress')) {
            return 0;
        }

        $days = MirrorProgress::forUser($user->id)
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
