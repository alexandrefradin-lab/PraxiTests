<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Une piste métier du référentiel PTP. Voir migration create_career_paths_table
 * et PLAN-PISTES-DYNAMIQUES-PTP.md.
 */
class CareerPath extends Model
{
    // Règle projet #3 — fillable explicite, pas de $guarded=[]
    protected $fillable = [
        'slug',
        'title',
        'family',
        'rome_code',
        'rncp_codes',
        'formation_months',
        'market_demand',
        'market_trend',
        'salary_indicative',
        'fit_dimensions',
        'active',
    ];

    protected $casts = [
        'rncp_codes'        => 'array',
        'salary_indicative' => 'array',
        'fit_dimensions'    => 'array',
        'formation_months'  => 'integer',
        'active'            => 'boolean',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(ProfilePathMatch::class);
    }
}
