<?php

namespace Praxis\Core\Journey;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Cadence mutualisée « un jour = une pratique » pour les mini-apps adossées à
 * leurs PROPRES tables (PraxiLead, PraxiVision, PraxiZenith, PraxiMiroir…),
 * par opposition au JourneyEngine qui sert les parcours « registre »
 * (table partagée journey_starts / journey_progress).
 *
 * Le parcours dure totalDays() jours. Le jour J se débloque J-1 jours
 * calendaires après l'inscription (started_on). Le jour 1 est donc disponible
 * immédiatement, le jour 2 le lendemain, etc.
 *
 * Les jours passés restent accessibles (rattrapage), les jours futurs sont
 * verrouillés. Aucune dépense d'Éclats : la seule clé, c'est le temps.
 *
 * Chaque plugin fournit uniquement sa configuration (modèles, table, durée) :
 * la mécanique — création du parcours, jour courant, déblocage, streak —
 * vit ici, une seule fois.
 */
abstract class DailyJourneyService
{
    // ─── Configuration à fournir par chaque plugin ───────────────────────────

    /** Classe du modèle « parcours » (une ligne par utilisateur : user_id, started_on). */
    abstract protected function journeyModel(): string;

    /** Classe du modèle de progression quotidienne (user_id, day_index, completed_at…). */
    abstract protected function progressModel(): string;

    /** Nom de la table de progression (garde-fou Schema::hasTable dans streakFor). */
    abstract protected function progressTable(): string;

    /** Durée du parcours en jours (60 en général, 30 pour PraxiMiroir). */
    abstract public function totalDays(): int;

    // ─── Mécanique commune ───────────────────────────────────────────────────

    /**
     * Récupère (ou crée à la première visite) le parcours de l'utilisateur.
     */
    public function journeyFor(User $user): Model
    {
        $journeyModel = $this->journeyModel();

        return $journeyModel::firstOrCreate(
            ['user_id' => $user->id],
            ['started_on' => Carbon::today()],
        );
    }

    /**
     * Numéro du jour de parcours actuellement atteint (1..totalDays).
     */
    public function currentDay(Model $journey): int
    {
        $elapsed = $journey->started_on->startOfDay()->diffInDays(Carbon::today(), false);
        $day     = $elapsed + 1;

        return max(1, min($this->totalDays(), $day));
    }

    /**
     * Le jour {dayIndex} est-il débloqué pour ce parcours ?
     */
    public function isUnlocked(Model $journey, int $dayIndex): bool
    {
        return $dayIndex >= 1
            && $dayIndex <= $this->totalDays()
            && $dayIndex <= $this->currentDay($journey);
    }

    /**
     * Nombre de jours calendaires à attendre avant que {dayIndex} se débloque.
     */
    public function daysUntilUnlock(Model $journey, int $dayIndex): int
    {
        return max(0, $dayIndex - $this->currentDay($journey));
    }

    /**
     * Série en cours : nombre de jours consécutifs (jusqu'à aujourd'hui ou hier)
     * pour lesquels un jour de parcours a été marqué comme fait.
     */
    public function streakFor(User $user): int
    {
        if (! Schema::hasTable($this->progressTable())) {
            return 0;
        }

        $progressModel = $this->progressModel();

        $days = $progressModel::forUser($user->id)
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

        // La série n'est « vivante » que si le dernier jour fait date
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
