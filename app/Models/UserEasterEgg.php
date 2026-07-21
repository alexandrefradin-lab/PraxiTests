<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un secret découvert par un utilisateur. L'unicité (user_id, slug) est
 * garantie en base — c'est elle qui porte l'anti-replay, pas le code.
 */
class UserEasterEgg extends Model
{
    protected $fillable = ['user_id', 'slug', 'claimed_at'];

    protected $casts = ['claimed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
