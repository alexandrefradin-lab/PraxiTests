<?php

namespace Praxis\Plugins\PraxiBalance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Compteurs globaux d'un candidat sur La Balance.
 *
 * `sessions_count` est l'horloge de la répétition espacée : une notion
 * redevient due quand son `due_session` a été rattrapé par ce compteur.
 */
class BalanceProfile extends Model
{
    protected $table = 'balance_profiles';

    protected $fillable = [
        'user_id',
        'sessions_count',
        'points',
        'mean_rt_ms',
        'rt_samples',
    ];

    protected $casts = [
        'sessions_count' => 'integer',
        'points'         => 'integer',
        'mean_rt_ms'     => 'integer',
        'rt_samples'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Intègre les temps de réaction d'une série chronométrée à la moyenne
     * courante, sans conserver les mesures individuelles.
     */
    public function addReactionTimes(array $samples): void
    {
        $samples = array_filter($samples, fn ($ms) => $ms > 0 && $ms < 10000);

        if ($samples === []) {
            return;
        }

        $total = ($this->mean_rt_ms ?? 0) * $this->rt_samples + array_sum($samples);
        $count = $this->rt_samples + count($samples);

        $this->mean_rt_ms = (int) round($total / $count);
        $this->rt_samples = $count;
    }
}
