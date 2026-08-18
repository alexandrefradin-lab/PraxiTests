<?php

namespace Praxis\Plugins\PraxiGuet\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Niveau validé par un candidat, avec son meilleur score.
 */
class GuetLevelProgress extends Model
{
    protected $table = 'guet_level_progress';

    protected $fillable = [
        'user_id',
        'level',
        'best_score',
        'completed_at',
        'eclats_awarded',
    ];

    protected $casts = [
        'level'          => 'integer',
        'best_score'     => 'integer',
        'completed_at'   => 'datetime',
        'eclats_awarded' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }
}
