<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginLog extends Model
{
    protected $table = 'plugin_logs';

    protected $fillable = [
        'plugin_id',
        'level',
        'event',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }
}
