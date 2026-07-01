<?php

namespace Praxis\Core\Journey;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de parcours mutualisé — déverrouillage TEMPOREL commun à tous les
 * parcours 60 jours (PraxiFlow, PraxiLink, PraxiSelf, PraxiSpeak, PraxiZen…).
 *
 * Un jour se débloque à sa date calendaire : jour N accessible quand N jours
 * (moins un) se sont écoulés depuis `started_on`. La date de démarrage est
 * créée à la première visite du tableau de bord (table `journey_starts`).
 *
 * La progression réelle (complété, ressenti, notes, streak) vit dans
 * App\Models\JourneyProgress (table `journey_progress`). Ce moteur ne gère que
 * le rythme d'ouverture des jours.
 */
class JourneyEngine
{
    public const TOTAL_DAYS = 60;
    public const ECLATS_PER_PRACTICE = 20;

    /**
     * Date de démarrage du parcours pour cet utilisateur (créée à la 1re visite).
     */
    public function startedOn(int $userId, string $slug): Carbon
    {
        $row = DB::table('journey_starts')
            ->where('user_id', $userId)
            ->where('plugin_slug', $slug)
            ->first();

        if ($row) {
            return Carbon::parse($row->started_on);
        }

        $today = Carbon::today();
        DB::table('journey_starts')->insert([
            'user_id'    => $userId,
            'plugin_slug'=> $slug,
            'started_on' => $today->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $today;
    }

    /**
     * Jour « courant » = jour du parcours ouvert aujourd'hui (borné 1..TOTAL_DAYS).
     */
    public function currentDay(int $userId, string $slug): int
    {
        $elapsed = $this->startedOn($userId, $slug)
            ->startOfDay()
            ->diffInDays(Carbon::today(), false);

        return max(1, min(self::TOTAL_DAYS, $elapsed + 1));
    }

    public function isUnlocked(int $userId, string $slug, int $day): bool
    {
        return $day >= 1
            && $day <= self::TOTAL_DAYS
            && $day <= $this->currentDay($userId, $slug);
    }

    public function daysUntilUnlock(int $userId, string $slug, int $day): int
    {
        return max(0, $day - $this->currentDay($userId, $slug));
    }
}
