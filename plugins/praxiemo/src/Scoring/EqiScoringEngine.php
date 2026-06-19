<?php

namespace Praxis\Plugins\PraxiEmo\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
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

        // Étalonnage par dimension
        $normScores = [];
        foreach ($scoresDim as $dimId => $raw) {
            $normScores[$dimId] = NormInterpreter::enrich('praxiemo-eqi', (string) $dimId, $raw);
        }

        return [
            'engine'        => $this->key(),
            'dim_scores'    => $scoresDim,
            'norm_scores'   => $normScores,
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
        // Convention Bar-On : les 6 items DS (indices 80-85) sont formulés positivement.
        // Un score ÉLEVÉ indique que le répondant se présente sous un jour très favorable = biais élevé.
        // Plage totale : 6 (min) à 24 (max).
        //   >= 20 → Biais fort   (le répondant surreprésente systématiquement ses qualités)
        //   >= 15 → Biais modéré (légère tendance à la présentation avantageuse)
        //   <  15 → Fiable       (pas de distorsion significative détectée)
        if ($sum >= 20) {
            return [
                'score'   => $sum,
                'niveau'  => 'Biais fort',
                'alerte'  => true,
                'message' => "Vos réponses indiquent une forte tendance à vous présenter sous un jour très favorable. Les scores obtenus reflètent peut-être davantage l'image que vous souhaitez donner que votre vécu émotionnel quotidien.",
            ];
        }
        if ($sum >= 15) {
            return ['score' => $sum, 'niveau' => 'Biais modéré', 'alerte' => false, 'message' => ''];
        }
        return ['score' => $sum, 'niveau' => 'Fiable', 'alerte' => false, 'message' => ''];
    }
}
