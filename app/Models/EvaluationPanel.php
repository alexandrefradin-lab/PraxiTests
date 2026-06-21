<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Panel d'évaluation 360° d'un candidat (le « sujet ») pour un test donné.
 * Regroupe les invitations envoyées aux évaluateurs et pointe vers
 * l'auto-évaluation de référence.
 */
class EvaluationPanel extends Model
{
    protected $fillable = [
        'user_id',
        'test_id',
        'self_attempt_id',
        'status',
        'anonymity_threshold',
        'closed_at',
    ];

    protected $casts = [
        'closed_at'           => 'datetime',
        'anonymity_threshold' => 'integer',
    ];

    public const RELATIONS = ['manager', 'pair', 'collaborateur'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function selfAttempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'self_attempt_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EvaluationInvitation::class, 'panel_id');
    }

    /** Tentatives « invitées » (regards des évaluateurs) rattachées au panel. */
    public function raterAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'panel_id')
            ->where('rater_relation', '!=', 'self');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /** Nombre d'évaluateurs ayant complété leur réponse. */
    public function completedRaterCount(): int
    {
        return $this->invitations()->where('status', 'completed')->count();
    }
}
