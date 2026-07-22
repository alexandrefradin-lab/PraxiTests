<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProtectionAlert — journal des anomalies du dispositif anti-copie.
 *
 * Distinct de AuditLog (actions admin légitimes) : ici on ne consigne que du
 * comportement suspect, pour qu'une revue hebdomadaire tienne en une requête.
 *
 * Usage :
 *   ProtectionAlert::raise('scraping', 'Cadence anormale : 340 pages en 10 min', [...]);
 */
class ProtectionAlert extends Model
{
    public const TYPE_SCRAPING = 'scraping';
    public const TYPE_SHARING  = 'sharing';
    public const TYPE_LICENSE  = 'license';
    public const TYPE_PDF_LEAK = 'pdf_leak';

    protected $fillable = [
        'user_id', 'type', 'severity', 'ip_address', 'user_agent',
        'summary', 'context', 'resolved_at',
    ];

    protected $casts = [
        'context'     => 'array',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistre une alerte.
     *
     * Anti-inondation : une même signature (type + utilisateur + IP) n'est
     * consignée qu'une fois par heure. Sans cela, un aspirateur qui tape
     * 3 000 pages produirait 3 000 lignes et rendrait la table inexploitable.
     */
    public static function raise(
        string $type,
        string $summary,
        array $context = [],
        string $severity = 'warning',
        ?int $userId = null,
    ): ?static {
        $request = request();
        $ip      = $request?->ip();
        $userId ??= $request?->user()?->id;

        $signature = 'protection:alert:' . $type . ':' . ($userId ?? 'anon') . ':' . ($ip ?? 'unknown');

        if (\Illuminate\Support\Facades\Cache::has($signature)) {
            return null;
        }

        \Illuminate\Support\Facades\Cache::put($signature, true, 3600);

        return static::create([
            'user_id'    => $userId,
            'type'       => $type,
            'severity'   => $severity,
            'ip_address' => $ip,
            'user_agent' => substr((string) $request?->userAgent(), 0, 255) ?: null,
            'summary'    => $summary,
            'context'    => $context,
        ]);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }
}
