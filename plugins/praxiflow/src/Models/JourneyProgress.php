<?php

namespace Praxis\Plugins\PraxiFlow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        'completed_at'    => 'datetime',
        'day'             => 'integer',
        'duration_actual' => 'integer',
        'felt_score'      => 'integer',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPlugin(Builder $query, string $slug): Builder
    {
        return $query->where('plugin_slug', $slug);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    // ─── Méthodes statiques ───────────────────────────────────────────────────

    /**
     * Retourne le numéro du prochain jour non complété (1 si aucun jour fait).
     */
    public static function currentDay(int $userId, string $slug): int
    {
        $maxCompleted = static::query()
            ->forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->max('day');

        return $maxCompleted ? min($maxCompleted + 1, 60) : 1;
    }

    /**
     * Retourne le streak actuel (jours consécutifs complétés en partant d'aujourd'hui).
     */
    public static function streakFor(int $userId, string $slug): int
    {
        $completedDays = static::query()
            ->forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->orderByDesc('completed_at')
            ->pluck('completed_at', 'day')
            ->toArray();

        if (empty($completedDays)) {
            return 0;
        }

        // Tri par date décroissante
        arsort($completedDays);

        $streak    = 0;
        $lastDate  = now()->startOfDay();

        foreach ($completedDays as $day => $completedAt) {
            $date = \Carbon\Carbon::parse($completedAt)->startOfDay();
            $diff = $lastDate->diffInDays($date);

            if ($diff <= 1) {
                $streak++;
                $lastDate = $date;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Retourne le taux de complétion (0.0 à 100.0) sur 60 jours.
     */
    public static function completionRate(int $userId, string $slug): float
    {
        $completed = static::query()
            ->forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->count();

        return round(($completed / 60) * 100, 1);
    }
}
