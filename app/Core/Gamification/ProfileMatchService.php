<?php

namespace Praxis\Core\Gamification;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Évalue la pertinence d'une mini-app (reward) pour un utilisateur donné.
 *
 * Stratégie : on cherche les mots-clés déclarés dans le bloc `profile_match`
 * du plugin.json à l'intérieur du Grimoire de l'utilisateur (synthèse + texte
 * des voies). Plus il y a de correspondances, plus le score est élevé.
 *
 * - Aucun Grimoire → aucune recommandation (pas de faux positifs)
 * - Grimoire sans voies (synthèse seule) → matching partiel sur la synthèse
 * - Seuil de recommandation : ≥ 2 mots-clés trouvés (robustesse)
 */
class ProfileMatchService
{
    /** Nombre minimum de mots-clés pour qualifier une app de "recommandée". */
    private const RECOMMEND_THRESHOLD = 2;

    /**
     * Calcule la pertinence d'une mini-app pour l'utilisateur.
     *
     * @param  User  $user
     * @param  array $profileMatch  Contenu du bloc `profile_match` du plugin.json
     * @return array{recommended: bool, match_score: int, match_reason: string|null}
     */
    public function evaluate(User $user, array $profileMatch): array
    {
        $noop = ['recommended' => false, 'match_score' => 0, 'match_reason' => null];

        if (empty($profileMatch['keywords'])) {
            return $noop;
        }

        $grimoire = $user->profileGrimoire;

        // Pas de Grimoire ou pas encore prêt → on ne peut pas matcher
        if (! $grimoire || $grimoire->status !== 'ready') {
            return $noop;
        }

        // Corpus de texte dans lequel chercher : synthèse + textes des voies
        $corpus = $this->buildCorpus($grimoire);

        if (empty($corpus)) {
            return $noop;
        }

        $keywords = array_map('mb_strtolower', $profileMatch['keywords']);
        $hits     = 0;

        foreach ($keywords as $kw) {
            if (mb_strpos($corpus, $kw) !== false) {
                $hits++;
            }
        }

        $score         = count($keywords) > 0
            ? (int) min(100, round(($hits / count($keywords)) * 100))
            : 0;
        $recommended   = $hits >= self::RECOMMEND_THRESHOLD;
        $reason        = $recommended ? ($profileMatch['reason'] ?? null) : null;

        return [
            'recommended'  => $recommended,
            'match_score'  => $score,
            'match_reason' => $reason,
        ];
    }

    /**
     * Construit un corpus textuel normalisé (minuscules, sans accents optionnels)
     * à partir de la synthèse et des voies du Grimoire.
     */
    private function buildCorpus(\App\Models\ProfileGrimoire $grimoire): string
    {
        $parts = [];

        // Synthèse globale
        if (! empty($grimoire->synthesis)) {
            $parts[] = $grimoire->synthesis;
        }

        // Voies : titre + secteur + pourquoi
        if (! empty($grimoire->voies) && is_array($grimoire->voies)) {
            foreach ($grimoire->voies as $voie) {
                foreach (['titre', 'secteur', 'pourquoi', 'modele', 'prochaine_etape'] as $field) {
                    if (! empty($voie[$field]) && is_string($voie[$field])) {
                        $parts[] = $voie[$field];
                    }
                }
            }
        }

        return mb_strtolower(implode(' ', $parts));
    }
}
