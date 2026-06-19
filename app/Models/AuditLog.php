<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog — Traçabilité des actions administrateur.
 *
 * Table : audit_logs (déjà créée en migration 2026_04_27_000008)
 * Colonnes : user_id, action, resource_type, resource_id, metadata (JSON), ip_address, user_agent
 *
 * #9 — Câblage de la table audit_logs pour tracer les modifications destructives
 * réalisées par les administrateurs (modification/suppression de tests, leads, campagnes).
 */
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistre une action admin en une seule ligne.
     *
     * Usage :
     *   AuditLog::record('test.destroyed', $test, ['slug' => $test->slug]);
     */
    public static function record(
        string $action,
        ?object $resource = null,
        array $metadata = [],
    ): static {
        $request = request();

        return static::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'resource_type' => $resource ? class_basename($resource) : null,
            'resource_id'   => $resource?->id ?? null,
            'metadata'      => $metadata ?: null,
            'ip_address'    => $request?->ip(),
            'user_agent'    => $request?->userAgent(),
        ]);
    }
}
