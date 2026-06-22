<?php

namespace Praxis\Plugins\PraxiLead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MgmtJourney extends Model
{
    protected $table = 'mgmt_journeys';

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
