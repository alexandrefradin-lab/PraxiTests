<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $guarded = [];
    protected $casts = ['criteria' => 'array', 'hidden' => 'boolean'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')->withPivot(['earned_at', 'context']);
    }
}
