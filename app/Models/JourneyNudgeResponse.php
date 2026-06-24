<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyNudgeResponse extends Model
{
    protected $table = 'journey_nudge_responses';

    protected $fillable = [
        'user_id',
        'plugin',
        'day',
        'q1_obstacle',
        'q2_category',
        'q3_score',
        'q4_friend_advice',
        'q5_small_step',
    ];

    protected $casts = [
        'day'      => 'integer',
        'q3_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
