<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TestInvitation extends Model
{
    protected $fillable = [
        'test_id',
        'professional_account_id',
        'email',
        'first_name',
        'last_name',
        'token',
        'status',
        'metadata',
        'sent_at',
        'opened_at',
        'expires_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'sent_at'    => 'datetime',
        'opened_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $inv) {
            $inv->token ??= Str::random(48);
            $inv->expires_at ??= now()->addDays(30);
        });
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function professionalAccount(): BelongsTo
    {
        return $this->belongsTo(ProfessionalAccount::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
