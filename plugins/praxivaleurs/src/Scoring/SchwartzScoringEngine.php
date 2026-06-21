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

        // Moyenne brute par dimension (échelle 1-6).
        $dimMean = [];
        foreach ($dims as $key => $_) {
            $dimMean[$key] = $counts[$key] > 0 ? $sums[$key] / $counts[$key] : 1;
        }

        // Correction ipsative de Schwartz (centrage MRAT). Sans elle, l'effet
        // plafond/acquiescement fait ressortir presque toutes les valeurs à
        // 70-100 % : on mesure alors un niveau d'approbation, pas des PRIORITÉS.
        // On centre chaque valeur sur la moyenne individuelle du répondant
        // (50 = priorité moyenne ; >50 = valeur prioritaire ; <50 = secondaire).
        $mrat = array_sum($counts) > 0 ? array_sum($sums) / array_sum($counts) : 3.5;

        $likertNorm = [];   // brut conservé pour référence/affichage
        $ipsative   = [];   // priorité relative centrée → 0-100
        foreach ($dims as $key => $_) {
            $likertNorm[$key] = (int) round((($dimMean[$key] - 1) / 5) * 100);
            $centered = $dimMean[$key] - $mrat;                       // ≈ -2..+2
            $ipsative[$key] = (int) round(max(0, min(100, 50 + $centered * 20)));
        }

        $tournoiNorm = $this->tournoiScores($attempt);

        // Score final = priorité ipsative, légèrement ajustée par le tournoi
        // de comparaisons par paires s'il existe (poids réduit 80/20 : le
        // tournoi force gagnant=100/perdant=0, à ne pas laisser dominer).
        $finals = [];
        foreach ($dims as $key => $_) {
            if ($tournoiNorm) {
                $finals[$key] = (int) round($ipsative[$key] * 0.8 + ($tournoiNorm[$key] ?? 0) * 0.2);
            } else {
                $finals[$key] = $ipsative[$key];
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
            'ipsative_norm' => $ipsative,
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
