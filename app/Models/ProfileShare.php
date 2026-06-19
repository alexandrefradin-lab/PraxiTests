<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProfileShare extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'view_count',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateFor(User $user, int $daysValid = 30): self
    {
        // Désactive les anciens liens actifs
        self::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return self::create([
            'user_id'    => $user->id,
            'token'      => Str::random(48),
            'expires_at' => now()->addDays($daysValid),
            'is_active'  => true,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function incrementView(): void
    {
        $this->increment('view_count');
    }

    public function getShareUrlAttribute(): string
    {
        return route('profile.shared', $this->token);
    }
}
