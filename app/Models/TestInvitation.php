<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TestInvitation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'test_id',
        'professional_account_id',
        'email',
        'first_name',
        'last_name',
        'token',
        'status',
        'metadata',
        'sent_at',
        'opened_at',
        'expires_at',
        'consent_share_professional',
        'consent_given_at',
    ];

    protected $casts = [
        'metadata'                   => 'array',
        'sent_at'                    => 'datetime',
        'opened_at'                  => 'datetime',
        'expires_at'                 => 'datetime',
        'consent_given_at'           => 'datetime',
        'consent_share_professional' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $inv) {
            // MIN-3: Le token d'invitation est un secret d'URL à usage unique (48 caractères
            // aléatoires = ~286 bits d'entropie). Il n'est PAS un mot de passe réutilisable
            // et n'est valable que 30 jours. Le stocker en clair est acceptable car :
            //   1. Il sert uniquement à authentifier la visite d'un lien (pas de login).
            //   2. Il est consommé via la session dès l'ouverture (opened_at marqué).
            //   3. Il expire et n'est pas réutilisable après complétion.
            // Si une attaque DB directe est dans le modèle de menace, envisager HMAC-SHA256.
            $inv->token ??= Str::random(48);
            $inv->expires_at ??= now()->addDays(30);
        });

        static::created(function (self $inv) {
            // MET-C2 — Envoyer l'email d'invitation au candidat dès la création.
            // send() et non queue() : le cron OVH ne draine la file qu'une fois
            // par heure — l'invitation part en direct via l'API Brevo (HTTP).
            // updateQuietly() évite de re-déclencher les événements Eloquent.
            if ($inv->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($inv->email)
                        ->send(new \App\Mail\CandidateInvitationMail($inv));
                    // Marquer l'invitation comme "envoyée" dès que le job est dispatché
                    // (pour sync : c'est déjà parti ; pour async : c'est en file).
                    $inv->updateQuietly([
                        'sent_at' => now(),
                        'status'  => 'sent',
                    ]);
                } catch (\Throwable $e) {
                    report($e);
                    // En cas d'échec d'envoi, on ne change pas le statut (reste pending)
                }
            }
        });
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function professionalAccount(): BelongsTo
    {
        return $this->belongsTo(ProfessionalAccount::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
