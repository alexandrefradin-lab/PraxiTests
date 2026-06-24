<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestResult extends Model
{
    use SoftDeletes;

    // #3 — $guarded=[] remplacé par $fillable explicite (protection mass assignment)
    protected $fillable = [
        'attempt_id',
        'scoring',
        'ai_synthesis',
        'suggested_jobs',
        'strengths',
        'growth_areas',
        'insights_unlocked',
        'ai_metadata',
        'ai_driver',
        'ai_model',
        'ai_tokens_used',
        'generated_at',
        'ai_generated_at',
    ];

    protected $casts = [
        'scoring'           => 'array',
        'suggested_jobs'    => 'array',
        'strengths'         => 'array',
        'growth_areas'      => 'array',
        'insights_unlocked' => 'array',
        'ai_metadata'       => 'array',
        'generated_at'      => 'datetime',
        'ai_generated_at'   => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    /**
     * Retourne le disclaimer IA à afficher dans l'interface.
     * Expose au candidat les éléments ayant guidé les recommandations,
     * conformément aux bonnes pratiques de transparence algorithmique.
     */
    public function aiDisclaimer(): array
    {
        $meta = $this->ai_metadata ?? [];
        return [
            'model'           => $this->ai_model ?? $this->ai_driver ?? 'IA',
            'generated_at'    => ($this->ai_generated_at ?? $this->generated_at)?->toIso8601String(),
            'tests_analyzed'  => $meta['tests_count'] ?? null,
            'dimensions_used' => $meta['dimensions'] ?? [],
            'prompt_version'  => $meta['prompt_version'] ?? null,
            'disclaimer_text' => 'Ces recommandations sont générées par intelligence artificielle '
                . 'à partir de vos résultats aux tests. Elles constituent une aide à la réflexion '
                . 'et ne remplacent pas l\'accompagnement d\'un professionnel de l\'orientation.',
        ];
    }
}
