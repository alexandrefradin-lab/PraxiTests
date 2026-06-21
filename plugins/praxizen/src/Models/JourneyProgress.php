<?php

namespace Praxis\Plugins\PraxiZen\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle de progression dans le parcours 60 jours.
 *
 * @property int         $id
 * @property int         $user_id
 * @property string      $plugin_slug
 * @property int         $day
 * @property \Carbon\Carbon|null $completed_at
 * @property int|null    $duration_actual   en secondes
 * @property int|null    $felt_score        1-5
 * @property string|null $notes
 */
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
        'day'             => 'integer',
        'completed_at'    => 'datetime',
        'duration_actual' => 'integer',
        'felt_score'      => 'integer',
    ];

    // ─── Relations ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

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

    // ─── Méthodes statiques utiles ───────────────────────────────────────

    /**
     * Retourne le prochain jour non complété pour un utilisateur et un plugin donnés.
     * Retourne 1 si aucune progression, 61 si le parcours est terminé.
     */
    public static function currentDay(int $userId, string $slug): int
    {
        $completedDays = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->pluck('day')
            ->all();

        for ($day = 1; $day <= 60; $day++) {
            if (! in_array($day, $completedDays, true)) {
                return $day;
            }
        }

        return 61; // parcours terminé
    }

    /**
     * Retourne le streak actuel (jours consécutifs depuis le dernier jour complété).
     */
    public static function currentStreak(int $userId, string $slug): int
    {
        $entries = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->orderByDesc('completed_at')
            ->get(['completed_at']);

        if ($entries->isEmpty()) {
            return 0;
        }

        // Jours calendaires DISTINCTS où au moins un item a été complété,
        // du plus récent au plus ancien (dédup pour ne pas gonfler le streak
        // quand plusieurs items sont complétés le même jour).
        $days = $entries
            ->map(fn ($e) => $e->completed_at->startOfDay())
            ->unique(fn ($d) => $d->toDateString())
            ->values();

        $streak = 0;
        $prev   = now()->startOfDay()->addDay(); // demain pour que aujourd'hui compte

        foreach ($days as $date) {
            // Jour consécutif au précédent (exactement 1 jour d'écart).
            if ((int) abs($prev->diffInDays($date)) === 1) {
                $streak++;
                $prev = $date;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Retourne le nombre de jours complétés pour un utilisateur et un plugin donnés.
     */
    public static function completedCount(int $userId, string $slug): int
    {
        return static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->count();
    }
}
