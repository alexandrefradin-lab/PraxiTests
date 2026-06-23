<?php

namespace Praxis\Plugins\PraxiZenith\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocusJourney extends Model
{
    protected $table = 'focus_journeys';

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
