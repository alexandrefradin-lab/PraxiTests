<?php

namespace Praxis\Plugins\PraxiVision\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisionJourney extends Model
{
    protected $table = 'vision_journeys';

    protected $fillable = [
        'user_id',
        'started_on',
    ];

    protected $casts = [
        'started_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
