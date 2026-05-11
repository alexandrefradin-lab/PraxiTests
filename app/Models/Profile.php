<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'preferences'      => 'array',
        'metadata'         => 'array',
        'cv_structured'    => 'array',
        'consent_data'     => 'boolean',
        'consent_marketing' => 'boolean',
        'status_since'     => 'date',
        'completed_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        return $this->status
            && $this->status_since
            && $this->cv_path
            && $this->consent_data;
    }
}
