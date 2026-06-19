<?php

namespace Praxis\Plugins\Praxis360\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\Praxis360\Data\Questions;

/**
 * Moteur de scoring Praxis 360 — auto-évaluation soft skills.
 *
 * Logique reprise du WP (Praxis360_Scoring::compute) pour la branche « self » :
 * moyenne simple des items de chaque dimension sur l'échelle de fréquence 1-5.
 *
 * Différence avec le template : l'échelle est 1-5 (et non 1-4), donc la
 * normalisation 0-100 et l'éventuelle inversion sont adaptées en conséquence.
 */
class Praxis360ScoringEngine implements ScoringEngineContract
{
    /** Bornes de l'échelle de fréquence (1-5). */
    private const SCALE_MIN = 1;
    private const SCALE_MAX = 5;

    public function key(): string
    {
        return 'praxis360-softskills';
    }

    public function score(TestAttempt $attempt): array
    {
        // ── 1. Initialiser les accumulateurs ──────────────────────────────
        $dims   = Questions::dimensions();
        $sums   = array_fill_keys(array_keys($dims), 0);
        $counts = array_fill_keys(array_keys($dims), 0);

        // ── 2. Parcourir les réponses ─────────────────────────────────────
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring   = $answer->question->scoring ?? [];
            $dimension = $scoring['dimension'] ?? null;
            $reversed  = (bool) ($scoring['reversed'] ?? false);
            $weight    = (float) ($scoring['weight'] ?? 1);

            if (!$dimension || !isset($sums[$dimension])) {
                continue;
            }

            // Valeur brute attendue 1-5. Une réponse vide est ignorée (équivalent
            // du « Non observé » du WP, exclu de la moyenne).
            if ($answer->value === null || $answer->value === '') {
                continue;
            }

            $val = max(self::SCALE_MIN, min(self::SCALE_MAX, (int) $answer->value));

            // Inversion éventuelle (aucun item inversé dans le référentiel actuel).
            if ($reversed) {
                $val = (self::SCALE_MIN + self::SCALE_MAX) - $val; // 6 - val
            }

            $sums[$dimension]   += $val * $weight;
            $counts[$dimension] += $weight;
        }

        // ── 3. Scores bruts (moyenne 1-5) + normalisés (0-100) ────────────
        $rawScores  = [];
        $normalized = [];
        $span       = self::SCALE_MAX - self::SCALE_MIN; // 4

        foreach ($dims as $dimKey => $_) {
            $n   = $counts[$dimKey];
            $avg = $n > 0 ? $sums[$dimKey] / $n : (float) self::SCALE_MIN;

            $rawScores[$dimKey]  = round($avg, 2);
            $normalized[$dimKey] = (int) round(($avg - self::SCALE_MIN) / $span * 100);
        }

        // ── 4. Étalonnage — percentile + label candidat ───────────────────
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = NormInterpreter::enrich($this->key(), $dimKey, $raw);
        }

        // ── 5. Score global (moyenne des dimensions normalisées) ──────────
        $globalScore = (int) round(array_sum($normalized) / count($normalized));

        // ── 6. Forces / axes de progrès (sur les moyennes brutes) ─────────
        $ranked = $rawScores;
        arsort($ranked);
        $strengths = array_slice(array_keys($ranked), 0, 3);
        asort($ranked);
        $improvements = array_slice(array_keys($ranked), 0, 3);

        // ── 7. Retour ─────────────────────────────────────────────────────
        return [
            'engine'       => $this->key(),
            'dimensions'   => $normalized,   // { communication: 72, ... }
            'raw_scores'   => $rawScores,    // moyennes brutes 1-5
            'norm_scores'  => $normScores,   // étalonnage
            'global_score' => $globalScore,  // 0-100
            'strengths'    => $strengths,    // 3 dimensions les plus fortes
            'improvements' => $improvements, // 3 axes de progrès
            'meta'         => $dims,          // libellés + couleurs
            'computed_at'  => now()->toIso8601String(),
        ];
    }
}
