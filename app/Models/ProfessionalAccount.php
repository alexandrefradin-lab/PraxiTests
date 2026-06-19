<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfessionalAccount extends Model
{
    use SoftDeletes;

    // #3 — $guarded=[] remplacé par $fillable explicite (protection mass assignment)
    protected $fillable = [
        'owner_user_id',
        'company_name',
        'subdomain',
        'custom_domain',
        'plan',
        'branding',
        'settings',
        'seats_limit',
        'trial_ends_at',
        'subscribed_until',
    ];

    protected $casts = [
        'branding'         => 'array',
        'settings'         => 'array',
        'trial_ends_at'    => 'datetime',
        'subscribed_until' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'professional_account_users')
            ->withPivot('role')->withTimestamps();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
