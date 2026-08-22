<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Achat one-shot d'un particulier (offre B2C — config/b2c.php).
 * Cycle de vie : pending (checkout créé) → paid (retour checkout ou webhook).
 */
class CandidatePurchase extends Model
{
    protected $fillable = [
        'user_id',
        'product',
        'amount',
        'currency',
        'status',
        'stripe_session_id',
        'cgv_accepted_at',
        'withdrawal_waived_at',
        'paid_at',
    ];

    protected $casts = [
        'cgv_accepted_at'      => 'datetime',
        'withdrawal_waived_at' => 'datetime',
        'paid_at'              => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /** Idempotent : le retour de checkout et le webhook peuvent tous deux appeler markPaid(). */
    public function markPaid(): void
    {
        if ($this->status === 'paid') {
            return;
        }

        $this->update([
            'status'  => 'paid',
            'paid_at' => $this->paid_at ?? now(),
        ]);
    }
}
