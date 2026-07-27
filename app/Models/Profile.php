<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    // #3 — $guarded=[] remplacé par $fillable explicite (protection mass assignment)
    protected $fillable = [
        'user_id',
        'quest_title',
        'status',
        'status_since',
        'status_months',
        'current_role',
        'industry',
        'work_sector',
        'hobbies',
        'problematique',
        'cv_path',
        'cv_original_name',
        'cv_extracted_text',
        'cv_structured',
        'preferences',
        'metadata',
        'consent_data',
        'consent_marketing',
        'marketing_unsubscribed_at',
        'completed_at',
    ];

    protected $casts = [
        'preferences'       => 'array',
        'metadata'          => 'array',
        'cv_structured'     => 'array',
        'consent_data'      => 'boolean',
        'consent_marketing' => 'boolean',
        'marketing_unsubscribed_at' => 'datetime',
        'status_since'      => 'date',
        'completed_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un profil est complet si le candidat a renseigné son statut, son
     * ancienneté, fourni son Codex de compétences (CV déposé OU saisie
     * manuelle des 3 infos) et accepté le traitement des données.
     * (Onboarding obligatoire avant de passer un test.)
     */
    public function isComplete(): bool
    {
        return (bool) (
            $this->status
            && $this->status_since
            && ($this->cv_path || $this->cv_structured)
            && $this->consent_data
        );
    }
}
