<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scoring'           => 'array',
        'suggested_jobs'    => 'array',
        'strengths'         => 'array',
        'growth_areas'      => 'array',
        'insights_unlocked' => 'array',
        'generated_at'      => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }
}
