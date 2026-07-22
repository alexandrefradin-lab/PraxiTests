<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserDevice — un appareil observé sur un compte.
 *
 * Sert à repérer le partage ou la revente d'accès : un consultant travaille
 * sur deux ou trois appareils, un compte mutualisé entre dix structures en
 * accumule bien davantage, depuis autant de réseaux différents.
 */
class UserDevice extends Model
{
    protected $fillable = [
        'user_id', 'fingerprint', 'label', 'last_ip', 'last_network',
        'hits', 'first_seen_at', 'last_seen_at', 'trusted',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
        'trusted'       => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Appareils vus sur la fenêtre d'observation, hors appareils validés. */
    public function scopeRecent($query, int $days)
    {
        return $query->where('last_seen_at', '>=', now()->subDays($days))
            ->where('trusted', false);
    }
}
