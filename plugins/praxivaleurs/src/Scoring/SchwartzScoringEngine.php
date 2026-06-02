<?php

namespace Praxis\Plugins\PraxiValeurs\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PraxiValeurs\Data\Values;

class SchwartzScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxivaleurs-schwartz';
    }

    public function score(TestAttempt $attempt): array
    {
        $dims = Values::dimensions();
        $sums = array_fill_keys(array_keys($dims), 0);
        $counts = array_fill_keys(array_keys($dims), 0);

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $dim = $answer->question->scoring['dimension'] ?? null;
            if (!$dim || !isset($sums[$dim])) {
                continue;
            }
            $val = max(1, min(6, (int) $answer->value));
            $sums[$dim] += $val;
            $counts[$dim]++;
        }

        // Score 0-100 par dim (échelle Likert 1-6 → 0-100).
        $likertNorm = [];
        foreach ($dims as $key => $_) {
            $moy = $counts[$key] > 0 ? $sums[$key] / $counts[$key] : 0;
            $likertNorm[$key] = (int) round(($moy / 6) * 100);
        }

        $tournoiNorm = $this->tournoiScores($attempt);

        // Pondéré : 60% Likert + 40% tournoi (si présent), sinon 100% Likert.
        $finals = [];
        foreach ($dims as $key => $_) {
            if ($tournoiNorm) {
                $finals[$key] = (int) round($likertNorm[$key] * 0.6 + ($tournoiNorm[$key] ?? 0) * 0.4);
            } else {
                $finals[$key] = $likertNorm[$key];
            }
            $finals[$key] = max(0, min(100, $finals[$key]));
        }

        arsort($finals);
        $top5 = array_slice($finals, 0, 5, true);

        // Étalonnage par valeur
        $normScores = [];
        foreach ($finals as $key => $score) {
            $normScores[$key] = NormInterpreter::enrich('praxivaleurs-schwartz', $key, $score);
        }

        return [
            'engine'        => $this->key(),
            'dimensions'    => $finals,
            'norm_scores'   => $normScores,
            'top5'          => $top5,
            'meta'          => $dims,
            'likert_norm'   => $likertNorm,
            'tournoi_norm'  => $tournoiNorm,
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    /** Si un tournoi de comparaisons existe en `progress` du attempt, on l'extrait. */
    protected function tournoiScores(TestAttempt $attempt): ?array
    {
        $tournoi = $attempt->progress['tournoi'] ?? null;
        if (!is_array($tournoi) || !$tournoi) {
            return null;
        }
        $victoires = array_fill_keys(array_keys(Values::dimensions()), 0);
        foreach ($tournoi as $comp) {
            $w = $comp['winner'] ?? null;
            if ($w && isset($victoires[$w])) {
                $victoires[$w]++;
            }
        }
        $max = max($victoires) ?: 1;
        return array_map(fn ($v) => (int) round(($v / $max) * 100), $victoires);
    }
}
