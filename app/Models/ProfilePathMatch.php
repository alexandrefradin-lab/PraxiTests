<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Résultat calculé d'une piste pour un profil donné (cache recalculable).
 * Voir migration create_profile_path_matches_table et PLAN-PISTES-DYNAMIQUES-PTP.md.
 */
class ProfilePathMatch extends Model
{
    // Règle projet #3 — fillable explicite, pas de $guarded=[]
    protected $fillable = [
        'profile_id',
        'career_path_id',
        'fit_score',
        'formation_gap_months',
        'tier',
        'opportunity_index',
        'unlocked',
        'computed_at',
    ];

    protected $casts = [
        'fit_score'            => 'integer',
        'formation_gap_months' => 'integer',
        'opportunity_index'    => 'integer',
        'unlocked'             => 'boolean',
        'computed_at'          => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function careerPath(): BelongsTo
    {
        return $this->belongsTo(CareerPath::class);
    }
}
