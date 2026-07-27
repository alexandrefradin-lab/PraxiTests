<?php

namespace Praxis\Plugins\PraxiCog\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PraxiCog\Data\Questions;

/**
 * Scoring PraxiCog — test d'aptitude : chaque item est JUSTE (1) ou FAUX (0).
 *
 * ≠ moteurs Likert (personnalité) : on ne moyenne pas des échelles, on compte
 * des bonnes réponses. Le score brut d'une dimension = % de bonnes réponses,
 * converti en percentile indicatif par NormInterpreter (normes provisoires
 * seedées, recalculées automatiquement par la plateforme après 50 passations).
 *
 * ⚠ Aucun « QI » n'est produit — uniquement des niveaux et un profil indicatif.
 */
class PraxiCogScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxicog-scoring';
    }

    public function score(TestAttempt $attempt): array
    {
        $dims    = Questions::dimensions();
        $correct = array_fill_keys(array_keys($dims), 0);
        $total   = array_fill_keys(array_keys($dims), 0);

        $timeSum   = 0;
        $timeCount = 0;

        // ── 1. Dépouillement item par item ────────────────────────────────
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring   = $answer->question->scoring ?? [];
            $dimension = $scoring['dimension'] ?? null;

            if (!$dimension || !array_key_exists($dimension, $total)) {
                continue;
            }

            $total[$dimension]++;

            // Réponse juste ? (value = -1 → timeout / non répondu → faux)
            $expected = $scoring['correct'] ?? null;
            if ($expected !== null && (int) $answer->value === (int) $expected) {
                $correct[$dimension]++;
            }

            // Temps de traitement (colonne time_spent_seconds, alimentée par le runner).
            if ($answer->time_spent_seconds !== null) {
                $timeSum += (int) $answer->time_spent_seconds;
                $timeCount++;
            }
        }

        // ── 2. Scores bruts (% de bonnes réponses) par dimension ──────────
        $rawScores   = [];
        $normalized  = [];
        $perDimension = [];

        foreach ($dims as $dimKey => $meta) {
            $n   = $total[$dimKey];
            $ok  = $correct[$dimKey];
            $pct = $n > 0 ? (int) round($ok / $n * 100) : 0;

            $rawScores[$dimKey]   = $pct;   // unité des normes : % de réussite (0-100)
            $normalized[$dimKey]  = $pct;
            $perDimension[$dimKey] = [
                'correct' => $ok,
                'total'   => $n,
                'percent' => $pct,
                'label'   => $meta['label'],
                'color'   => $meta['color'],
            ];
        }

        // ── 3. Score global ───────────────────────────────────────────────
        $totalOk  = array_sum($correct);
        $totalNb  = array_sum($total);
        $global   = $totalNb > 0 ? (int) round($totalOk / $totalNb * 100) : 0;
        $rawScores['global'] = $global;

        // ── 4. Étalonnage → percentiles + niveaux indicatifs ──────────────
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = NormInterpreter::enrich('praxicog-scoring', $dimKey, (float) $raw);
        }

        // ── 5. Vitesse de traitement (indicateur secondaire) ──────────────
        $avg = $timeCount > 0 ? round($timeSum / $timeCount, 1) : null;
        $speed = [
            'total_seconds' => $timeSum,
            'avg_seconds'   => $avg,
            'label'         => $this->speedLabel($avg),
        ];

        // ── 6. Retour ─────────────────────────────────────────────────────
        return [
            'engine'        => $this->key(),
            'dimensions'    => $normalized,     // { logique: 66, verbal: 83, … } — % réussite
            'raw_scores'    => $rawScores,       // idem + 'global' (base de recalcul des normes)
            'norm_scores'   => $normScores,      // percentile + niveau indicatif par dimension et global
            'global_score'  => $global,
            'global_norm'   => $normScores['global'] ?? null,
            'per_dimension' => $perDimension,
            'correct_count' => $totalOk,
            'item_count'    => $totalNb,
            'speed'         => $speed,
            'meta'          => $dims,
            'disclaimer'    => 'Indicateur d\'aptitude au raisonnement, à visée d\'orientation et de discussion. '
                             . 'Il ne constitue pas une mesure de QI clinique ni un diagnostic.',
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    /** Étiquette qualitative de vitesse (sans jargon, non pénalisante). */
    private function speedLabel(?float $avg): ?string
    {
        if ($avg === null) {
            return null;
        }
        return match (true) {
            $avg <= 20 => 'Traitement rapide',
            $avg <= 40 => 'Rythme posé',
            default    => 'Rythme réfléchi',
        };
    }
}
