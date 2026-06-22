<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un message de la conversation avec l'Oracle (chat IA d'orientation).
 * Voir migration create_oracle_messages_table.
 */
class OracleMessage extends Model
{
    // Règle projet #3 — fillable explicite, pas de $guarded=[]
    protected $fillable = [
        'user_id',
        'role',
        'content',
        'tokens',
    ];

    protected $casts = [
        'tokens' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
