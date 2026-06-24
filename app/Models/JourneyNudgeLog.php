<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyNudgeLog extends Model
{
    protected $table = 'journey_nudge_logs';

    protected $fillable = [
        'user_id',
        'plugin',
        'day',
        'nudged_on',
    ];

    protected $casts = [
        'nudged_on' => 'date',
        'day'       => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
