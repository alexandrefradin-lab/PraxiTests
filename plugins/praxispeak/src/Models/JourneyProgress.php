<?php

namespace Praxis\Plugins\PraxiSpeak\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // ── Relations ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

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

    // ── Méthodes statiques ─────────────────────────────────────────────────────

    /**
     * Retourne le numéro du jour courant (= prochain jour non complété).
     * Renvoie 1 si aucun jour n'a encore été validé.
     * Renvoie 61 si le parcours est terminé.
     */
    public static function currentDay(int $userId, string $slug): int
    {
        $maxCompleted = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->max('day');

        return $maxCompleted ? min($maxCompleted + 1, 61) : 1;
    }

    /**
     * Calcule le streak actuel (nombre de jours consécutifs complétés jusqu'à hier ou aujourd'hui).
     */
    public static function streakFor(int $userId, string $slug): int
    {
        $completedDays = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->orderByDesc('completed_at')
            ->pluck('completed_at', 'day')
            ->map(fn ($dt) => \Carbon\Carbon::parse($dt)->startOfDay());

        if ($completedDays->isEmpty()) {
            return 0;
        }

        $today     = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $streak    = 0;
        $cursor    = null;

        // Trie par date décroissante
        $sorted = $completedDays->sortDesc()->values();

        foreach ($sorted as $day => $date) {
            if ($cursor === null) {
                // Premier jour : doit être aujourd'hui ou hier pour que le streak soit actif
                if ($date->equalTo($today) || $date->equalTo($yesterday)) {
                    $cursor = $date;
                    $streak = 1;
                } else {
                    break;
                }
            } else {
                // Jours suivants : doit être la veille du curseur
                if ($date->equalTo($cursor->copy()->subDay())) {
                    $cursor = $date;
                    $streak++;
                } else {
                    break;
                }
            }
        }

        return $streak;
    }

    /**
     * Retourne le tableau de progression [day => bool] pour affichage de la grille.
     */
    public static function progressGrid(int $userId, string $slug): array
    {
        $completed = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->pluck('day')
            ->flip()
            ->map(fn () => true);

        $grid = [];
        for ($d = 1; $d <= 60; $d++) {
            $grid[$d] = $completed->has($d);
        }

        return $grid;
    }

    /**
     * Retourne les statistiques du parcours pour un utilisateur.
     */
    public static function statsFor(int $userId, string $slug): array
    {
        $records = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->get();

        $completedCount  = $records->count();
        $currentDay      = static::currentDay($userId, $slug);
        $streak          = static::streakFor($userId, $slug);
        $avgFelt         = $records->whereNotNull('felt_score')->avg('felt_score');
        $progressPct     = (int) round(($completedCount / 60) * 100);

        // Phase actuelle
        $phase = match (true) {
            $currentDay <= 15  => 'decouverte',
            $currentDay <= 30  => 'installation',
            $currentDay <= 45  => 'renforcement',
            default            => 'maitrise',
        };

        return [
            'completed_count' => $completedCount,
            'current_day'     => $currentDay,
            'progress_pct'    => $progressPct,
            'streak'          => $streak,
            'phase'           => $phase,
            'avg_felt_score'  => $avgFelt ? round($avgFelt, 1) : null,
            'is_complete'     => $completedCount >= 60,
        ];
    }
}
