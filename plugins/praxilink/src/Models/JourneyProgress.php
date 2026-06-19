<?php

namespace Praxis\Plugins\PraxiLink\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Modèle Eloquent pour le suivi du parcours 60 jours PraxiLink.
 *
 * @property int                    $id
 * @property int                    $user_id
 * @property string                 $plugin_slug
 * @property int                    $day
 * @property \Carbon\Carbon|null    $completed_at
 * @property int|null               $duration_actual
 * @property int|null               $felt_score
 * @property string|null            $notes
 * @property \Carbon\Carbon         $created_at
 * @property \Carbon\Carbon         $updated_at
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
        'completed_at'   => 'datetime',
        'day'            => 'integer',
        'duration_actual'=> 'integer',
        'felt_score'     => 'integer',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Filtre par utilisateur.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtre par plugin slug.
     */
    public function scopeForPlugin(Builder $query, string $slug): Builder
    {
        return $query->where('plugin_slug', $slug);
    }

    /**
     * Ne retourne que les jours complétés (completed_at non null).
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Méthodes statiques calculatoires
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Retourne le jour courant de l'utilisateur dans le parcours.
     * = dernier jour complété + 1, plafonné à 60.
     *
     * @param int    $userId
     * @param string $slug
     * @return int  1-60 (1 si aucun jour complété)
     */
    public static function currentDay(int $userId, string $slug): int
    {
        $lastCompleted = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->max('day');

        if ($lastCompleted === null) {
            return 1;
        }

        return min(60, (int) $lastCompleted + 1);
    }

    /**
     * Calcule le streak courant (jours consécutifs complétés jusqu'à aujourd'hui).
     * Un jour est considéré "aujourd'hui" si completed_at est dans les dernières 48h
     * (tolérance pour les utilisateurs qui sautent un jour).
     *
     * @param int    $userId
     * @param string $slug
     * @return int  Nombre de jours de streak (0 si aucun)
     */
    public static function streakFor(int $userId, string $slug): int
    {
        $completedDays = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->orderByDesc('day')
            ->pluck('completed_at', 'day')
            ->toArray();

        if (empty($completedDays)) {
            return 0;
        }

        // Trie par jour décroissant
        krsort($completedDays);

        $streak   = 0;
        $prevDate = Carbon::now()->startOfDay();

        foreach ($completedDays as $day => $completedAt) {
            $completedDate = Carbon::parse($completedAt)->startOfDay();
            $diff          = $prevDate->diffInDays($completedDate);

            if ($diff <= 1) {
                $streak++;
                $prevDate = $completedDate;
            } else {
                // Rupture du streak
                break;
            }
        }

        return $streak;
    }

    /**
     * Calcule le taux de complétion du parcours (0.0 – 100.0).
     *
     * @param int    $userId
     * @param string $slug
     * @return float  Pourcentage (ex : 45.0 pour 27/60 jours)
     */
    public static function completionRate(int $userId, string $slug): float
    {
        $completed = static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->count();

        return round(($completed / 60) * 100, 1);
    }

    /**
     * Retourne les jours complétés sous forme de tableau indexé par numéro de jour.
     * Utile pour construire la grille visuelle 10×6.
     *
     * @param int    $userId
     * @param string $slug
     * @return array<int, array{completed_at: string, felt_score: int|null}>
     */
    public static function completedDaysMap(int $userId, string $slug): array
    {
        return static::forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->get(['day', 'completed_at', 'felt_score'])
            ->keyBy('day')
            ->map(fn ($r) => [
                'completed_at' => $r->completed_at?->toIso8601String(),
                'felt_score'   => $r->felt_score,
            ])
            ->toArray();
    }
}
