<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Le Grimoire global d'un candidat : relecture transversale de tous ses tests.
 * 1 ligne par utilisateur. Voir migration create_profile_grimoires_table.
 */
class ProfileGrimoire extends Model
{
    // #3 (règle projet) — fillable explicite, pas de $guarded=[]
    protected $fillable = [
        'user_id',
        'synthesis',
        'voies',
        'tests_included',
        'tests_signature',
        'ai_driver',
        'ai_model',
        'ai_tokens_used',
        'ai_metadata',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'voies'          => 'array',
        'tests_included' => 'array',
        'ai_metadata'    => 'array',
        'generated_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    /**
     * Disclaimer IA — transparence algorithmique (symétrie avec TestResult).
     */
    public function aiDisclaimer(): array
    {
        $meta = $this->ai_metadata ?? [];
        return [
            'model'          => $this->ai_model ?? $this->ai_driver ?? 'IA',
            'generated_at'   => $this->generated_at?->toIso8601String(),
            'tests_analyzed' => $meta['tests_count'] ?? (is_array($this->tests_included) ? count($this->tests_included) : null),
            'prompt_version' => $meta['prompt_version'] ?? null,
            'disclaimer_text' => 'Cette relecture globale est générée par intelligence artificielle '
                . 'en croisant l\'ensemble de vos résultats aux tests. Elle constitue une aide à la '
                . 'réflexion et ne remplace pas l\'accompagnement d\'un professionnel de l\'orientation.',
        ];
    }
}
