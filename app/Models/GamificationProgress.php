<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationProgress extends Model
{
    protected $table = 'gamification_progress';
    protected $guarded = [];

    protected $casts = [
        'milestones_reached' => 'array',
        'insights_unlocked'  => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
