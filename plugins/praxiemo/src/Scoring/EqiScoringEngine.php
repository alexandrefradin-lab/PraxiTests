<?php

namespace Praxis\Plugins\PraxiEmo\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\Scoring\SocialDesirability;
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

        $ds = $this->desirabilite($byIdx);

        // Correction douce de désirabilité : en cas de biais de présentation,
        // on régresse chaque dimension vers le milieu d'échelle (12,5 sur 5-20)
        // pour ne pas survaloriser une auto-image flatteuse.
        $shrink = SocialDesirability::shrinkFactor($ds['niveau']);
        if ($shrink < 1.0) {
            foreach ($scoresDim as $dimId => $raw) {
                $scoresDim[$dimId] = (int) round(SocialDesirability::shrink($raw, 12.5, $shrink));
            }
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
            'disclaimer'    => "Ce bilan d'intelligence émotionnelle est un outil d'auto-réflexion "
                . "inspiré des grands modèles du domaine. Il est indépendant de l'EQ-i 2.0® "
                . "(MHS), instrument propriétaire distinct, et n'en reprend ni les items ni l'étalonnage.",
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    protected function interpretGlobal(int $score): array
    {
        // Bandes resserrées (échelle 80-320) : une réponse globalement positive
        // ne doit pas suffire à décrocher « QE Élevé ». Seuils provisoires, à
        // remplacer par des bandes empiriques dès que les normes sont disponibles.
        if ($score <= 150) return ['QE Faible',     "Votre intelligence émotionnelle est en construction. C'est une excellente base pour commencer un travail sur vous."];
        if ($score <= 215) return ['QE Modéré',     "Vous disposez de vraies ressources émotionnelles. Quelques zones méritent d'être renforcées pour libérer votre plein potentiel."];
        if ($score <= 280) return ['QE Élevé',      "Votre intelligence émotionnelle est un vrai atout. Vous gérez bien vos émotions et savez créer des relations de qualité."];
        return                    ['QE Très élevé', "Vous faites partie des profils à haute intelligence émotionnelle. Votre capacité à comprendre et réguler vos émotions est remarquable."];
    }

    /**
     * Items Marlowe-Crowne (indices 80-85), échelle 1-4 → plage 6-24.
     * Seuils et correction délégués au service partagé
     * Praxis\Core\Scoring\SocialDesirability (fort ≤ 12, modéré ≤ 18 ici).
     */
    protected function desirabilite(array $byIdx): array
    {
        $sum = 0;
        $answered = 0;
        for ($i = 80; $i <= 85; $i++) {
            if (isset($byIdx[$i])) {
                $sum += $byIdx[$i];
                $answered++;
            }
        }

        return SocialDesirability::fromControlSum(
            sum: $sum,
            answered: $answered,
            itemCount: 6,
            itemMax: 4,
            messageFort: "Vos réponses semblent orientées vers une image très positive de vous-même. Les scores ci-dessus reflètent peut-être davantage ce que vous souhaiteriez être que ce que vous vivez au quotidien. Un regard plus nuancé pourrait révéler des pistes de développement précieuses.",
        );
    }
}
