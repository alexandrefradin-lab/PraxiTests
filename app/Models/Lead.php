<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    // La colonne deleted_at existe depuis la migration d'origine ; le trait
    // manquait — delete() supprimait donc définitivement. Corbeille admin.
    use SoftDeletes;

    // #3 — $guarded=[] remplacé par $fillable explicite (protection mass assignment)
    protected $fillable = [
        'professional_account_id',
        'user_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'source',
        'score',
        'status',
        'utm',
        'metadata',
        'last_activity_at',
    ];

    protected $casts = [
        'utm'              => 'array',
        'metadata'         => 'array',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function professionalAccount(): BelongsTo
    {
        return $this->belongsTo(ProfessionalAccount::class);
    }
}
