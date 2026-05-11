<?php

namespace Praxis\Plugins\PraxiEmo\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Plugins\PraxiEmo\Data\Dimensions;

class EqiScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxiemo-eqi';
    }

    public function score(TestAttempt $attempt): array
    {
        // Récolte réponses indexées par idx (0-85).
        $byIdx = [];
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $idx = $answer->question->scoring['idx'] ?? null;
            if ($idx !== null) $byIdx[(int) $idx] = max(1, min(4, (int) $answer->value));
        }

        $dims     = Dimensions::dimensions();
        $families = Dimensions::families();
        $scoresDim = [];
        foreach ($dims as $dimId => $info) {
            $sum = 0;
            foreach ($info['questions'] as $q) {
                $sum += $byIdx[$q] ?? 1;
            }
            $scoresDim[$dimId] = $sum; // 5-20
        }

        $scoreGlobal = array_sum($scoresDim); // 80-320

        // Top forces (3 plus hauts) et axes développement (≤12)
        arsort($scoresDim);
        $sorted = array_keys($scoresDim);
        $topForces = array_slice($sorted, 0, 3);
        $topDev = [];
        foreach (array_reverse($sorted) as $dimId) {
            if (count($topDev) >= 3) break;
            if ($scoresDim[$dimId] <= 12) $topDev[] = $dimId;
        }

        [$niveau, $phrase] = $this->interpretGlobal($scoreGlobal);
        $ds = $this->desirabilite($byIdx);

        return [
            'engine'        => $this->key(),
            'dim_scores'    => $scoresDim,
            'score_global'  => $scoreGlobal,
            'score_max'     => 320,
            'niveau_qe'     => $niveau,
            'phrase_qe'     => $phrase,
            'top_forces'    => $topForces,
            'top_dev'       => $topDev,
            'desirabilite'  => $ds,
            'meta_dimensions' => $dims,
            'meta_families' => $families,
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    protected function interpretGlobal(int $score): array
    {
        if ($score <= 120) return ['QE Faible',     "Votre intelligence émotionnelle est en construction. C'est une excellente base pour commencer un travail sur vous."];
        if ($score <= 200) return ['QE Modéré',     "Vous disposez de vraies ressources émotionnelles. Quelques zones méritent d'être renforcées pour libérer votre plein potentiel."];
        if ($score <= 280) return ['QE Élevé',      "Votre intelligence émotionnelle est un vrai atout. Vous gérez bien vos émotions et savez créer des relations de qualité."];
        return                    ['QE Très élevé', "Vous faites partie des profils à haute intelligence émotionnelle. Votre capacité à comprendre et réguler vos émotions est remarquable."];
    }

    protected function desirabilite(array $byIdx): array
    {
        $sum = 0;
        for ($i = 80; $i <= 85; $i++) {
            $sum += $byIdx[$i] ?? 1;
        }
        if ($sum <= 12) {
            return [
                'score'   => $sum,
                'niveau'  => 'Biais fort',
                'alerte'  => true,
                'message' => "Vos réponses semblent orientées vers une image très positive de vous-même. Les scores reflètent peut-être davantage ce que vous souhaiteriez être que ce que vous vivez au quotidien.",
            ];
        }
        if ($sum <= 18) {
            return ['score' => $sum, 'niveau' => 'Biais modéré', 'alerte' => false, 'message' => ''];
        }
        return ['score' => $sum, 'niveau' => 'Fiable', 'alerte' => false, 'message' => ''];
    }
}
