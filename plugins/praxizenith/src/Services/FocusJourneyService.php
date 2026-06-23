<?php

namespace Praxis\Plugins\PraxiZenith\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Praxis\Plugins\PraxiZenith\Models\FocusJourney;
use Praxis\Plugins\PraxiZenith\Models\FocusExerciseProgress;

/**
 * Cadence « un exercice de concentration par jour ».
 *
 * Le parcours dure 60 jours. L'exercice du jour J se débloque J-1 jours
 * calendaires après l'inscription (started_on). Le jour 1 est donc disponible
 * immédiatement, le jour 2 le lendemain, etc.
 *
 * Les jours passés restent accessibles (rattrapage), les jours futurs sont
 * verrouillés. Aucune dépense d'Éclats à l'intérieur du parcours : la seule
 * clé, c'est le temps. (L'accès au Sanctuaire lui-même est, lui, débloqué par
 * paliers d'Éclats cumulés via La Salle du Trésor — cf. reward.threshold_eclats.)
 */
class FocusJourneyService
{
    public const TOTAL_DAYS = 60;
    public const ECLATS_PER_EXERCISE = 15;

    /**
     * Récupère (ou crée à la première visite) le parcours de l'utilisateur.
     */
    public function journeyFor(User $user): FocusJourney
    {
        return FocusJourney::firstOrCreate(
            ['user_id' => $user->id],
            ['started_on' => Carbon::today()],
        );
    }

    /**
     * Numéro du jour de parcours actuellement atteint (1..60).
     */
    public function currentDay(FocusJourney $journey): int
    {
        $elapsed = $journey->started_on->startOfDay()->diffInDays(Carbon::today(), false);
        $day = $elapsed + 1;

        return max(1, min(self::TOTAL_DAYS, $day));
    }

    /**
     * L'exercice du jour {dayIndex} est-il débloqué pour ce parcours ?
     */
    public function isUnlocked(FocusJourney $journey, int $dayIndex): bool
    {
        return $dayIndex >= 1
            && $dayIndex <= self::TOTAL_DAYS
            && $dayIndex <= $this->currentDay($journey);
    }

    /**
     * Nombre de jours calendaires à attendre avant que {dayIndex} se débloque.
     */
    public function daysUntilUnlock(FocusJourney $journey, int $dayIndex): int
    {
        return max(0, $dayIndex - $this->currentDay($journey));
    }

    /**
     * Série en cours : nombre de jours consécutifs (jusqu'à aujourd'hui ou hier)
     * pour lesquels un exercice a été marqué comme fait.
     */
    public function streakFor(User $user): int
    {
        if (! Schema::hasTable('focus_exercise_progress')) {
            return 0;
        }

        $days = FocusExerciseProgress::forUser($user->id)
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

        // La série n'est « vivante » que si le dernier exercice fait date
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
