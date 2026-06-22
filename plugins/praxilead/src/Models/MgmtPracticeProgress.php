<?php

namespace Praxis\Plugins\PraxiLead\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MgmtPracticeProgress extends Model
{
    protected $table = 'mgmt_practice_progress';

    protected $fillable = [
        'user_id',
        'day_index',
        'completed_at',
        'felt_score',
        'notes',
        'eclats_awarded',
    ];

    protected $casts = [
        'day_index'      => 'integer',
        'completed_at'   => 'datetime',
        'felt_score'     => 'integer',
        'eclats_awarded' => 'boolean',
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
