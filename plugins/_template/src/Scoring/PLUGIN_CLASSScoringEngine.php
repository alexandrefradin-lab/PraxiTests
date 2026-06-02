<?php

namespace Praxis\Plugins\PLUGIN_CLASS\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PLUGIN_CLASS\Data\Questions;

class PLUGIN_CLASSScoringEngine implements ScoringEngineContract
{
    /**
     * Clé unique du moteur — doit correspondre à scoring_engine dans la table tests.
     * Convention : PLUGIN_SLUG-nom-du-scoring  (ex: praximet-riasec)
     */
    public function key(): string
    {
        return 'PLUGIN_SLUG-scoring';
    }

    public function score(TestAttempt $attempt): array
    {
        // ── 1. Initialiser les accumulateurs ──────────────────────────────
        $dims    = Questions::dimensions();
        $sums    = array_fill_keys(array_keys($dims), 0);
        $counts  = array_fill_keys(array_keys($dims), 0);

        // ── 2. Parcourir les réponses ─────────────────────────────────────
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring   = $answer->question->scoring ?? [];
            $dimension = $scoring['dimension'] ?? null;
            $reversed  = (bool) ($scoring['reversed'] ?? false);
            $weight    = (float) ($scoring['weight'] ?? 1);

            if (!$dimension || !isset($sums[$dimension])) {
                continue;
            }

            // Valeur brute (supposée 1-4 pour scale, int pour single)
            $val = max(1, min(4, (int) $answer->value));

            // Inversion si nécessaire (ex: item négatif)
            if ($reversed) {
                $val = 5 - $val;
            }

            $sums[$dimension]   += $val * $weight;
            $counts[$dimension] += $weight;
        }

        // ── 3. Calculer scores normalisés (0-100) par dimension ───────────
        $rawScores  = [];
        $normalized = [];

        foreach ($dims as $dimKey => $_) {
            $n = $counts[$dimKey];
            // Score brut : moyenne des items (1-4), ramené à 0-100
            $avg             = $n > 0 ? $sums[$dimKey] / $n : 1.0;
            $rawScores[$dimKey]  = round($avg, 2);
            $normalized[$dimKey] = (int) round(($avg - 1) / 3 * 100);
        }

        // ── 4. Étalonnage — enrichir avec percentile + label candidat ──────
        // NormInterpreter cherche les normes dans test_norms (seedées par NormsSeeder).
        // Si aucune norme : fallback silencieux (pas de dots/labels, score brut affiché).
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = NormInterpreter::enrich('PLUGIN_SLUG-scoring', $dimKey, $raw);
        }

        // ── 5. Profil global (optionnel) ───────────────────────────────────
        // Calculer un score global ou un profil typologique si besoin.
        $globalScore = (int) round(array_sum($normalized) / count($normalized));

        // ── 6. Retourner le résultat ───────────────────────────────────────
        // Conventions :
        //   'dimensions'  → utilisé par ResultsShow.vue générique (clé → valeur 0-100)
        //   'norm_scores' → étalonnage (clé → { percentile, label, dots, color })
        //   'meta'        → libellés des dimensions (utilisés par ResultsShow et PromptBuilder)
        return [
            'engine'       => $this->key(),
            'dimensions'   => $normalized,          // { dim_alpha: 72, dim_beta: 58 }
            'raw_scores'   => $rawScores,            // moyennes brutes
            'norm_scores'  => $normScores,           // étalonnage
            'global_score' => $globalScore,          // 0-100
            'meta'         => $dims,                 // libellés + couleurs
            'computed_at'  => now()->toIso8601String(),
        ];
    }
}
