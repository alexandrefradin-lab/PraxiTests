<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TestInvitation extends Model
{
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
    ];

    protected $casts = [
        'metadata'   => 'array',
        'sent_at'    => 'datetime',
        'opened_at'  => 'datetime',
        'expires_at' => 'datetime',
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
            // On vérifie que l'email est présent et que la relation test est chargeable.
            if ($inv->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($inv->email)
                        ->queue(new \App\Mail\CandidateInvitationMail($inv));
                } catch (\Throwable $e) {
                    report($e);
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
