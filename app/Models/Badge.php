<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    // cf. audit E-6 — $fillable explicite (protection mass assignment)
    protected $fillable = [
        'slug', 'name', 'name_corporate', 'description', 'description_corporate',
        'icon', 'criteria', 'xp_reward', 'hidden',
    ];
    protected $casts = ['criteria' => 'array', 'hidden' => 'boolean'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')->withPivot(['earned_at', 'context']);
    }

    /** Nom affiché selon le parcours, avec repli sur le libellé « quête ». */
    public function displayName(bool $corporate): string
    {
        return $corporate ? ($this->name_corporate ?: $this->name) : $this->name;
    }

    /** Description affichée selon le parcours, avec repli sur le libellé « quête ». */
    public function displayDescription(bool $corporate): ?string
    {
        return $corporate ? ($this->description_corporate ?: $this->description) : $this->description;
    }
}
