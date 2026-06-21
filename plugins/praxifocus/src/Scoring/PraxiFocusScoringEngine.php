<?php

namespace Praxis\Plugins\PraxiFocus\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PraxiFocus\Data\Questions;

/**
 * Moteur de scoring ASRS-v1.1 (Adult ADHD Self-Report Scale, OMS).
 *
 * Deux lectures complémentaires :
 *   1. Screener Partie A (items 1-6) : compte des « cases grisées » ASRS.
 *      ≥ 4 marques → symptômes hautement compatibles avec un TDAH adulte
 *      (= invitation à consulter, PAS un diagnostic).
 *   2. Profil descriptif : fréquence moyenne (0-4 → 0-100) sur deux
 *      dimensions, Inattention et Hyperactivité/Impulsivité.
 *
 * ⚠️ OUTIL DE REPÉRAGE. Le résultat ne constitue ni un diagnostic ni un
 * avis médical et doit être vérifié avec un professionnel de santé.
 */
class PraxiFocusScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxifocus-asrs';
    }

    public function score(TestAttempt $attempt): array
    {
        $dims   = Questions::dimensions();
        $sums   = array_fill_keys(array_keys($dims), 0.0);
        $counts = array_fill_keys(array_keys($dims), 0);

        $screenerScore = 0;   // Partie A : nb de cases grisées (0-6)
        $screenerMax   = 0;
        $partBBurden   = 0;   // Partie B : nb de symptômes à fréquence élevée (≥ Souvent)
        $partBMax      = 0;
        $allSum        = 0.0;
        $allCount      = 0;

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring   = $answer->question->scoring ?? [];
            $dimension = $scoring['dimension'] ?? null;
            $part      = $scoring['part'] ?? null;

            if (!$dimension || !isset($sums[$dimension])) {
                continue;
            }

            // Fréquence brute ASRS : 0 (Jamais) … 4 (Très souvent).
            $val = max(0, min(4, (int) $answer->value));

            $sums[$dimension]   += $val;
            $counts[$dimension] += 1;
            $allSum   += $val;
            $allCount += 1;

            if ($part === 'A') {
                $screenerMax++;
                $threshold = (int) ($scoring['screener_threshold'] ?? 3);
                if ($val >= $threshold) {
                    $screenerScore++;
                }
            } elseif ($part === 'B') {
                $partBMax++;
                if ($val >= 3) { // Souvent ou Très souvent
                    $partBBurden++;
                }
            }
        }

        // ── Scores par dimension (0-4 → 0-100) ────────────────────────────
        $rawScores  = [];
        $normalized = [];
        $bands      = [];

        foreach ($dims as $dimKey => $meta) {
            $n   = $counts[$dimKey];
            $avg = $n > 0 ? $sums[$dimKey] / $n : 0.0;
            $rawScores[$dimKey]  = round($avg, 2);
            $normalized[$dimKey] = (int) round($avg / 4 * 100);
            $bands[$dimKey]      = $this->band($normalized[$dimKey]);
        }

        // ── Étalonnage optionnel (aucune norme publiée par défaut) ────────
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = NormInterpreter::enrich($this->key(), $dimKey, $raw);
        }

        // ── Screener Partie A ─────────────────────────────────────────────
        $positive = $screenerScore >= 4;
        $screener = [
            'score'    => $screenerScore,
            'max'      => $screenerMax ?: 6,
            'positive' => $positive,
            'label'    => $positive
                ? 'Symptômes compatibles avec un TDAH'
                : 'Repérage non concluant',
            'summary'  => $positive
                ? "Sur les 6 questions les plus prédictives (Partie A de l'ASRS), {$screenerScore} réponses se situent dans la zone symptomatique. C'est un signal qui justifie d'en parler à un professionnel de santé — ce n'est pas un diagnostic."
                : "Sur les 6 questions les plus prédictives (Partie A de l'ASRS), {$screenerScore} réponses se situent dans la zone symptomatique (seuil de repérage : 4). Cela ne signifie pas l'absence de TDAH : si tu ressens une gêne au quotidien, parles-en à un professionnel.",
        ];

        $globalScore = $allCount > 0 ? (int) round($allSum / $allCount / 4 * 100) : 0;

        return [
            'engine'        => $this->key(),
            'dimensions'    => $normalized,   // { inattention: 0-100, hyperactivite_impulsivite: 0-100 }
            'raw_scores'    => $rawScores,    // moyennes 0-4
            'norm_scores'   => $normScores,
            'dimension_bands' => $bands,
            'global_score'  => $globalScore,
            'screener'      => $screener,
            'part_b_burden' => ['count' => $partBBurden, 'max' => $partBMax ?: 12],
            'meta'          => $dims,
            'disclaimer'    => "Cet outil est un test de repérage basé sur l'échelle ASRS-v1.1 de l'OMS. Il ne constitue ni un diagnostic ni un avis médical. Seul un professionnel de santé qualifié (médecin, psychiatre, neuropsychologue) peut établir un diagnostic de TDAH. Vérifie toujours tes résultats avec un professionnel.",
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    /**
     * Bande interprétative descriptive (fréquence des symptômes rapportés).
     */
    private function band(int $score): array
    {
        if ($score >= 67) {
            return ['level' => 'eleve',  'label' => 'Symptômes fréquents',     'color' => 'gold'];
        }
        if ($score >= 34) {
            return ['level' => 'modere', 'label' => 'Symptômes occasionnels',  'color' => 'navy'];
        }
        return ['level' => 'faible', 'label' => 'Symptômes peu fréquents', 'color' => 'slate'];
    }
}
