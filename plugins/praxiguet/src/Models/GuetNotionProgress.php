<?php

namespace Praxis\Plugins\PraxiGuet\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ancrage d'une notion pour un candidat : boîte de Leitner, échéance et
 * dernière formulation servie.
 */
class GuetNotionProgress extends Model
{
    protected $table = 'guet_notion_progress';

    /** Intervalles de réapparition, en sessions, indexés par boîte. */
    public const INTERVALS = [1, 1, 2, 4, 8];

    public const MAX_BOX = 4;

    protected $fillable = [
        'user_id',
        'notion_id',
        'box',
        'due_session',
        'variant_index',
    ];

    protected $casts = [
        'box'           => 'integer',
        'due_session'   => 'integer',
        'variant_index' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    /** Notions à réancrer à la session courante. */
    public function scopeDue(Builder $q, int $sessionsCount): Builder
    {
        return $q->where('due_session', '<=', $sessionsCount);
    }

    /**
     * Applique le résultat d'une carte : une réussite fait monter d'une boîte,
     * une erreur en fait redescendre d'une seule.
     *
     * La rétrogradation est volontairement douce. Remettre à zéro une notion
     * réussie quatre fois d'affilée impose quatre nouvelles réussites pour
     * revenir au point de départ : simulé sur les 24 notions à 85 % de
     * réussite, cela porte l'ancrage complet de 19 à 134 sessions et de 213 à
     * 3 194 cartes, sans bénéfice pédagogique — l'oubli d'une notion mûre ne
     * l'efface pas, il l'affaiblit.
     */
    public function grade(bool $correct, int $sessionsCount): void
    {
        $this->box = $correct
            ? min($this->box + 1, self::MAX_BOX)
            : max(0, $this->box - 1);

        $this->due_session = $sessionsCount + self::INTERVALS[$this->box];
    }
}
