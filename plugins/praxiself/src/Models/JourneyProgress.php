<?php

namespace Praxis\Plugins\PraxiSelf\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Eloquent — journey_progress.
 *
 * @property int         $id
 * @property int         $user_id
 * @property string      $plugin_slug
 * @property int         $day
 * @property \Carbon\Carbon|null $completed_at
 * @property int|null    $duration_actual
 * @property int|null    $felt_score       (1–5)
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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

    // ─── Relations ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

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
     * Retourne le jour actuel du parcours pour un utilisateur.
     * = dernier jour complété + 1, minimum 1, maximum 60.
     */
    public static function currentDay(int $userId, string $slug): int
    {
        $lastCompleted = static::query()
            ->forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->max('day');

        if ($lastCompleted === null) {
            return 1;
        }

        return min(60, $lastCompleted + 1);
    }

    /**
     * Calcule le streak actuel (jours consécutifs complétés depuis aujourd'hui).
     * Parcourt les jours complétés du plus récent au plus ancien.
     * Deux jours sont "consécutifs" s'ils sont complétés dans les 48h l'un de l'autre.
     */
    public static function streakFor(int $userId, string $slug): int
    {
        $completedDates = static::query()
            ->forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->orderByDesc('completed_at')
            ->pluck('completed_at')
            ->map(fn ($dt) => \Carbon\Carbon::parse($dt)->startOfDay());

        if ($completedDates->isEmpty()) {
            return 0;
        }

        // Si le dernier jour complété n'est ni aujourd'hui ni hier → streak rompu
        $today     = \Carbon\Carbon::today();
        $lastDate  = $completedDates->first();

        if ($lastDate->diffInDays($today) > 1) {
            return 0;
        }

        $streak  = 1;
        $current = $lastDate;

        foreach ($completedDates->skip(1) as $date) {
            if ($current->diffInDays($date) <= 1) {
                $streak++;
                $current = $date;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Retourne le tableau des jours complétés (tableau de int) pour l'affichage de la grille.
     */
    public static function completedDays(int $userId, string $slug): array
    {
        return static::query()
            ->forUser($userId)
            ->forPlugin($slug)
            ->completed()
            ->pluck('day')
            ->map(fn ($d) => (int) $d)
            ->toArray();
    }

    /**
     * Marque un jour comme complété (upsert).
     */
    public static function markCompleted(
        int $userId,
        string $slug,
        int $day,
        ?int $durationActual = null,
        ?int $feltScore = null,
        ?string $notes = null
    ): static {
        return static::updateOrCreate(
            ['user_id' => $userId, 'plugin_slug' => $slug, 'day' => $day],
            [
                'completed_at'    => now(),
                'duration_actual' => $durationActual,
                'felt_score'      => $feltScore,
                'notes'           => $notes,
            ]
        );
    }
}
