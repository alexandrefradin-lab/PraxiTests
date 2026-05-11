<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $guarded = [];

    protected $casts = [
        'utm'              => 'array',
        'metadata'         => 'array',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function professionalAccount(): BelongsTo
    {
        return $this->belongsTo(ProfessionalAccount::class);
    }
}
