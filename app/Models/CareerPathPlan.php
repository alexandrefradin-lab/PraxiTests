<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Plan d'action IA pour une piste métier et un profil donnés.
 *
 * Généré une seule fois (Haiku), persisté pour éviter les coûts répétés.
 * plan_json = { premier_pas, etapes[], ressources[], conseil }
 */
class CareerPathPlan extends Model
{
    protected $fillable = [
        'profile_id',
        'career_path_id',
        'plan_json',
        'generated_at',
    ];

    protected $casts = [
        'plan_json'    => 'array',
        'generated_at' => 'datetime',
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
