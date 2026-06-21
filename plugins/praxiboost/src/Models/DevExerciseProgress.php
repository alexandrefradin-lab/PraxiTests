<?php

namespace Praxis\Plugins\PraxiBoost\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevExerciseProgress extends Model
{
    protected $table = 'dev_exercise_progress';

    protected $fillable = [
        'user_id',
        'exercise_slug',
        'unlocked_at',
        'completed_at',
        'felt_score',
        'notes',
    ];

    protected $casts = [
        'unlocked_at'  => 'datetime',
        'completed_at' => 'datetime',
        'felt_score'   => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
