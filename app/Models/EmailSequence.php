<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailSequence extends Model
{
    protected $table = 'email_sequences';

    protected $fillable = [
        'professional_account_id',
        'name',
        'trigger_event',
        'audience_filter',
        'steps',
        'enabled',
        'stats',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'steps'           => 'array',
        'stats'           => 'array',
        'enabled'         => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'sequence_id');
    }
}
