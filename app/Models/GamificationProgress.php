<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationProgress extends Model
{
    protected $table = 'gamification_progress';

    // cf. audit E-6 — $fillable explicite (protection mass assignment)
    protected $fillable = [
        'user_id',
        'test_id',
        'xp_total',
        'level',
        'milestones_reached',
        'insights_unlocked',
    ];

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
