<?php

namespace Praxis\Plugins\PraxiZen\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PraxiZen\Data\Exercises;

class PraxiZenScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxizen-stress';
    }

    public function score(TestAttempt $attempt): array
    {
        $dims   = Exercises::dimensions();
        $sums   = array_fill_keys(array_keys($dims), 0.0);
        $counts = array_fill_keys(array_keys($dims), 0.0);

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring   = $answer->question->scoring ?? [];
            $dimension = $scoring['dimension'] ?? null;
            $reversed  = (bool) ($scoring['reversed'] ?? false);
            $weight    = (float) ($scoring['weight'] ?? 1.0);

            if (! $dimension || ! isset($sums[$dimension])) {
                continue;
            }

            $val = max(1, min(4, (int) $answer->value));
            if ($reversed) {
                $val = 5 - $val;
            }

            $sums[$dimension]   += $val * $weight;
            $counts[$dimension] += $weight;
        }

        // Scores bruts (moyenne 1-4) et normalisés (0-100)
        $rawScores  = [];
        $normalized = [];

        foreach ($dims as $dimKey => $_) {
            $n                   = $counts[$dimKey];
            $avg                 = $n > 0 ? $sums[$dimKey] / $n : 1.0;
            $rawScores[$dimKey]  = round($avg, 2);
            $normalized[$dimKey] = (int) round(($avg - 1) / 3 * 100);
        }

        // Enrichissement normatif (percentile + label) via NormInterpreter
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = NormInterpreter::enrich($this->key(), $dimKey, $raw);
        }

        // Score global de bien-être (moyenne pondérée des 5 dimensions)
        $globalScore = (int) round(array_sum($normalized) / count($normalized));

        // Identification des dimensions faibles et fortes
        $sorted      = $normalized;
        asort($sorted);
        $weakDims    = array_slice(array_keys($sorted), 0, 2, true);
        $strongDims  = array_slice(array_keys(array_reverse($sorted, true)), 0, 2, true);

        // Exercices recommandés : ceux qui ciblent les dimensions les plus faibles
        $recommended = $this->recommendExercises($weakDims);

        // Niveau de stress global inversé (100 = pas de stress, 0 = stress maximal)
        $stressLevel = 100 - $globalScore;

        return [
            'engine'           => $this->key(),
            'dimensions'       => $normalized,
            'raw_scores'       => $rawScores,
            'norm_scores'      => $normScores,
            'global_score'     => $globalScore,
            'stress_level'     => $stressLevel,
            'wellness_label'   => $this->wellnessLabel($globalScore),
            'weak_dimensions'  => array_values($weakDims),
            'strong_dimensions'=> array_values($strongDims),
            'recommended'      => $recommended,
            'meta'             => $dims,
            'computed_at'      => now()->toIso8601String(),
        ];
    }

    // ─── Méthodes privées ────────────────────────────────────────────────────

    /**
     * Sélectionne les 3 exercices les plus pertinents pour les dimensions faibles.
     */
    private function recommendExercises(array $weakDimensions): array
    {
        $exercises = Exercises::all();
        $scored    = [];

        foreach ($exercises as $exercise) {
            $relevance = 0.0;
            foreach ($weakDimensions as $dim) {
                $relevance += (float) ($exercise['scoring'][$dim] ?? 0.0);
            }
            if ($relevance > 0) {
                $scored[] = ['exercise' => $exercise, 'relevance' => $relevance];
            }
        }

        usort($scored, fn ($a, $b) => $b['relevance'] <=> $a['relevance']);

        // Top 3 en priorisant la diversité des catégories, en 2 passes
        // pour garantir jusqu'à 3 exercices (la version précédente pouvait
        // en renvoyer moins de 3).
        $selected    = [];
        $selectedIds = [];
        $categories  = [];

        // Passe 1 : un exercice par catégorie distincte (relevance décroissante).
        foreach ($scored as $item) {
            if (count($selected) >= 3) {
                break;
            }
            $cat = $item['exercise']['category'];
            if (! isset($categories[$cat])) {
                $selected[]                       = $item['exercise'];
                $selectedIds[$item['exercise']['id']] = true;
                $categories[$cat]                 = true;
            }
        }

        // Passe 2 : complète jusqu'à 3 avec les meilleurs exercices restants.
        foreach ($scored as $item) {
            if (count($selected) >= 3) {
                break;
            }
            if (! isset($selectedIds[$item['exercise']['id']])) {
                $selected[]                           = $item['exercise'];
                $selectedIds[$item['exercise']['id']] = true;
            }
        }

        return $selected;
    }

    /**
     * Traduit le score global en label qualitatif.
     */
    private function wellnessLabel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Excellent équilibre',
            $score >= 65 => 'Bon équilibre',
            $score >= 50 => 'Équilibre à consolider',
            $score >= 35 => 'Vigilance recommandée',
            default      => 'Soutien prioritaire',
        };
    }
}
