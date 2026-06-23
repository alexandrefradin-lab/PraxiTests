<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    // cf. audit E-6 — $fillable explicite (protection mass assignment)
    protected $fillable = ['slug', 'name', 'description', 'icon', 'criteria', 'xp_reward', 'hidden'];
    protected $casts = ['criteria' => 'array', 'hidden' => 'boolean'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')->withPivot(['earned_at', 'context']);
    }
}
