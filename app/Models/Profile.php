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
        'status',
        'status_since',
        'status_months',
        'current_role',
        'industry',
        'cv_path',
        'cv_original_name',
        'cv_extracted_text',
        'cv_structured',
        'preferences',
        'metadata',
        'consent_data',
        'consent_marketing',
        'completed_at',
    ];

    protected $casts = [
        'preferences'       => 'array',
        'metadata'          => 'array',
        'cv_structured'     => 'array',
        'consent_data'      => 'boolean',
        'consent_marketing' => 'boolean',
        'status_since'      => 'date',
        'completed_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un profil est complet si le candidat a renseigné son statut, son
     * ancienneté, déposé son CV et accepté le traitement des données.
     * (Onboarding obligatoire avant de passer un test.)
     */
    public function isComplete(): bool
    {
        return (bool) (
            $this->status
            && $this->status_since
            && $this->cv_path
            && $this->consent_data
        );
    }
}
