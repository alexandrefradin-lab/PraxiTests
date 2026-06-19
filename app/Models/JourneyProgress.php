<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class JourneyProgress extends Model
{
    protected $table = 'journey_progress';

    protected $fillable = [
        'user_id',
        'plugin_slug',
        'day',
        'completed_at',
        'duration_actual',
        'felt_score',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'felt_score'   => 'integer',
    ];

    // ─── Static helpers ──────────────────────────────────────────────────────

    /**
     * Retourne le prochain jour non complété (1 si aucun jour complété).
     */
    public static function currentDay(int $userId, string $slug): int
    {
        $completed = static::where('user_id', $userId)
            ->where('plugin_slug', $slug)
            ->pluck('day')
            ->toArray();

        if (empty($completed)) {
            return 1;
        }

        for ($i = 1; $i <= 60; $i++) {
            if (!in_array($i, $completed, true)) {
                return $i;
            }
        }

        // Tous les 60 jours complétés
        return 60;
    }

    /**
     * Jours consécutifs depuis aujourd'hui ou hier (streak actif).
     */
    public static function streakFor(int $userId, string $slug): int
    {
        $records = static::where('user_id', $userId)
            ->where('plugin_slug', $slug)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get(['completed_at']);

        if ($records->isEmpty()) {
            return 0;
        }

        // Normaliser les dates complétées en dates calendaires uniques
        $dates = $records
            ->map(fn ($r) => Carbon::parse($r->completed_at)->toDateString())
            ->unique()
            ->values();

        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Le streak doit commencer aujourd'hui ou hier
        if ($dates[0] !== $today && $dates[0] !== $yesterday) {
            return 0;
        }

        $streak  = 1;
        $current = Carbon::parse($dates[0]);

        for ($i = 1; $i < $dates->count(); $i++) {
            $prev = Carbon::parse($dates[$i]);
            if ($current->diffInDays($prev) === 1) {
                $streak++;
                $current = $prev;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Retourne un tableau associatif des jours complétés : [1 => true, 3 => true, …]
     */
    public static function completedDays(int $userId, string $slug): array
    {
        return static::where('user_id', $userId)
            ->where('plugin_slug', $slug)
            ->whereNotNull('completed_at')
            ->pluck('day')
            ->mapWithKeys(fn ($day) => [$day => true])
            ->toArray();
    }

    /**
     * Taux de complétion en pourcentage (arrondi à 1 décimale).
     */
    public static function completionRate(int $userId, string $slug): float
    {
        $count = static::where('user_id', $userId)
            ->where('plugin_slug', $slug)
            ->whereNotNull('completed_at')
            ->count();

        return round(($count / 60) * 100, 1);
    }

    /**
     * Marque un jour comme complété et retourne l'entrée créée/mise à jour.
     */
    public static function markComplete(int $userId, string $slug, int $day, array $extra = []): self
    {
        return static::updateOrCreate(
            [
                'user_id'    => $userId,
                'plugin_slug'=> $slug,
                'day'        => $day,
            ],
            array_merge([
                'completed_at' => now(),
            ], $extra)
        );
    }
}
