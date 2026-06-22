<?php

namespace Praxis\Plugins\PraxiLead\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Praxis\Plugins\PraxiLead\Models\MgmtJourney;
use Praxis\Plugins\PraxiLead\Models\MgmtPracticeProgress;

/**
 * Cadence « une pratique par jour ».
 *
 * Le parcours dure 60 jours. La pratique du jour J du parcours se débloque
 * J-1 jours calendaires après l'inscription (started_on). Le jour 1 est donc
 * disponible immédiatement, le jour 2 le lendemain, etc.
 *
 * Les jours passés restent accessibles (rattrapage), les jours futurs sont
 * verrouillés. Aucune dépense d'Éclats : la seule clé, c'est le temps.
 */
class JourneyService
{
    public const TOTAL_DAYS = 60;
    public const ECLATS_PER_PRACTICE = 15;

    /**
     * Récupère (ou crée à la première visite) le parcours de l'utilisateur.
     */
    public function journeyFor(User $user): MgmtJourney
    {
        return MgmtJourney::firstOrCreate(
            ['user_id' => $user->id],
            ['started_on' => Carbon::today()],
        );
    }

    /**
     * Numéro du jour de parcours actuellement atteint (1..60).
     */
    public function currentDay(MgmtJourney $journey): int
    {
        $elapsed = $journey->started_on->startOfDay()->diffInDays(Carbon::today(), false);
        $day = $elapsed + 1;

        return max(1, min(self::TOTAL_DAYS, $day));
    }

    /**
     * La pratique du jour {dayIndex} est-elle débloquée pour ce parcours ?
     */
    public function isUnlocked(MgmtJourney $journey, int $dayIndex): bool
    {
        return $dayIndex >= 1
            && $dayIndex <= self::TOTAL_DAYS
            && $dayIndex <= $this->currentDay($journey);
    }

    /**
     * Nombre de jours calendaires à attendre avant que {dayIndex} se débloque.
     */
    public function daysUntilUnlock(MgmtJourney $journey, int $dayIndex): int
    {
        return max(0, $dayIndex - $this->currentDay($journey));
    }

    /**
     * Série en cours : nombre de jours consécutifs (jusqu'à aujourd'hui ou hier)
     * pour lesquels une pratique a été marquée comme faite.
     */
    public function streakFor(User $user): int
    {
        if (! Schema::hasTable('mgmt_practice_progress')) {
            return 0;
        }

        $days = MgmtPracticeProgress::forUser($user->id)
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

        // La série n'est « vivante » que si la dernière pratique faite date
        // d'aujourd'hui ou d'hier.
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
