<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Invitation d'un évaluateur dans un panel 360°. L'évaluateur répond de façon
 * anonyme via un lien tokenisé (sans compte). Son regard est stocké dans un
 * TestAttempt « invité » (attempt_id).
 */
class EvaluationInvitation extends Model
{
    protected $fillable = [
        'panel_id',
        'relation',
        'name',
        'email',
        'token',
        'status',
        'attempt_id',
        'verbatims',
        'sent_at',
        'opened_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'verbatims'    => 'array',
        'sent_at'      => 'datetime',
        'opened_at'    => 'datetime',
        'completed_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /** Libellés lisibles des relations. */
    public const RELATION_LABELS = [
        'manager'       => 'Manager',
        'pair'          => 'Pair / collègue',
        'collaborateur' => 'Collaborateur',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $inv) {
            $inv->token ??= Str::random(48);
            $inv->expires_at ??= now()->addDays(30);
        });
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(EvaluationPanel::class, 'panel_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function relationLabel(): string
    {
        return self::RELATION_LABELS[$this->relation] ?? $this->relation;
    }
}
