<?php

namespace Praxis\Plugins\PraxiSpeak\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Plugins\PraxiSpeak\Data\Exercises;

class PraxiSpeakScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxispeak-scoring';
    }

    public function score(TestAttempt $attempt): array
    {
        $exercises  = Exercises::exercises();
        $dimensions = Exercises::dimensions();
        $quotes     = Exercises::quotes();

        // Index des exercices par id
        $exerciseMap = [];
        foreach ($exercises as $ex) {
            $exerciseMap[$ex['id']] = $ex;
        }

        // Calcul des scores bruts par dimension
        $rawScores  = array_fill_keys(array_keys($dimensions), 0);
        $maxScores  = array_fill_keys(array_keys($dimensions), 0);
        $completed  = [];

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $exId = $answer->question->scoring['exercise_id'] ?? null;
            if (! $exId || ! isset($exerciseMap[$exId])) {
                continue;
            }
            $ex  = $exerciseMap[$exId];
            $dim = $ex['scoring']['dimension'];
            $pts = $ex['scoring']['points'];

            // La valeur de la réponse est entre 0 (non fait) et 1 (complété)
            $done = (int) filter_var($answer->value, FILTER_VALIDATE_BOOLEAN);
            if ($done) {
                $rawScores[$dim]  = ($rawScores[$dim]  ?? 0) + $pts;
                $completed[]       = $exId;
            }
            $maxScores[$dim] = ($maxScores[$dim] ?? 0) + $pts;
        }

        // Calcul des scores normalisés (0-100) par dimension
        $normScores = [];
        foreach ($dimensions as $dimId => $dimInfo) {
            $max = $maxScores[$dimId] ?? 0;
            $raw = $rawScores[$dimId] ?? 0;
            $normScores[$dimId] = $max > 0 ? (int) round(($raw / $max) * 100) : 0;
        }

        // Score global de l'orateur (0-100)
        $totalRaw = array_sum($rawScores);
        $totalMax = array_sum($maxScores);
        $globalScore = $totalMax > 0 ? (int) round(($totalRaw / $totalMax) * 100) : 0;

        // Interprétation du niveau
        [$niveau, $phrase] = $this->interpretGlobal($globalScore);

        // Dimension la plus forte et axe de développement prioritaire
        $dimsByScore = $normScores;
        arsort($dimsByScore);
        $topDim    = array_key_first($dimsByScore);
        $weakDim   = array_key_last($dimsByScore);

        // Exercice recommandé du jour : premier exercice non complété de la dimension la plus faible
        $recommendedExercise = null;
        foreach ($exercises as $ex) {
            if ($ex['scoring']['dimension'] === $weakDim && ! in_array($ex['id'], $completed, true)) {
                $recommendedExercise = $ex;
                break;
            }
        }

        // Citation aléatoire déterministe (basée sur l'id de l'attempt)
        $quoteIndex = $attempt->id % count($quotes);
        $quote      = $quotes[$quoteIndex];

        return [
            'engine'               => $this->key(),
            'dimensions'           => $dimensions,
            'raw_scores'           => $rawScores,
            'norm_scores'          => $normScores,
            'global_score'         => $globalScore,
            'niveau_orateur'       => $niveau,
            'phrase_orateur'       => $phrase,
            'top_dimension'        => $topDim,
            'weak_dimension'       => $weakDim,
            'completed_exercises'  => $completed,
            'recommended_exercise' => $recommendedExercise,
            'quote'                => $quote,
            'meta'                 => [
                'total_exercises'   => count($exercises),
                'completed_count'   => count($completed),
                'completion_pct'    => count($exercises) > 0
                    ? (int) round((count($completed) / count($exercises)) * 100)
                    : 0,
            ],
            'computed_at'          => now()->toIso8601String(),
        ];
    }

    /**
     * Le score reflète la PROGRESSION dans le parcours d'exercices (taux de
     * complétion), pas une évaluation de la performance oratoire. Les libellés
     * sont donc formulés en termes d'avancement, non de niveau de compétence
     * (audit 2026-06-21).
     */
    protected function interpretGlobal(int $score): array
    {
        if ($score < 20) {
            return [
                'Parcours entamé',
                "Tu viens de démarrer ton parcours. La prise de parole se travaille pas à pas — chaque exercice complété compte.",
            ];
        }
        if ($score < 45) {
            return [
                'Parcours en cours',
                "Tu avances dans le parcours. Continue les exercices pour ancrer durablement tes nouveaux réflexes.",
            ];
        }
        if ($score < 70) {
            return [
                'Parcours bien engagé',
                "Tu as réalisé une bonne partie des exercices. La régularité est la clé pour transformer l'essai.",
            ];
        }
        if ($score < 90) {
            return [
                'Parcours avancé',
                "Tu as complété la grande majorité des exercices. Bel engagement — garde le rythme.",
            ];
        }
        return [
            'Parcours complété',
            "Tu as parcouru l'ensemble des exercices d'entraînement. Bravo pour ta constance ! L'enjeu est maintenant d'entretenir la pratique.",
        ];
    }
}
