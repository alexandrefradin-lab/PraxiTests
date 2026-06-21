<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TestAttempt extends Model
{
    // #3 — $guarded=[] remplacé par $fillable explicite (protection mass assignment)
    protected $fillable = [
        'user_id',
        'test_id',
        'invitation_id',
        'panel_id',
        'rater_relation',
        'status',
        'current_section',
        'current_question',
        'time_spent_seconds',
        'progress',
        'started_at',
        'completed_at',
        'last_activity_at',
    ];

    protected $casts = [
        'progress'         => 'array',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(TestInvitation::class, 'invitation_id');
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(EvaluationPanel::class, 'panel_id');
    }

    /** Vrai si cette tentative est le regard d'un évaluateur (et non l'auto-évaluation). */
    public function isRater(): bool
    {
        return $this->rater_relation !== null && $this->rater_relation !== 'self';
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class, 'attempt_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(TestResult::class, 'attempt_id');
    }

    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    public function progressPercent(): float
    {
        $total = $this->test->totalQuestions();
        if ($total === 0) return 0;
        $answered = $this->answers()->count();
        return round(($answered / $total) * 100, 1);
    }
}
