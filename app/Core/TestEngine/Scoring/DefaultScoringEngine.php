<?php

namespace Praxis\Core\TestEngine\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;

/**
 * Moteur générique : agrège les `scoring` JSON de chaque question.
 * Format question.scoring attendu :
 *   { "dimension": "analytique", "weight": 1, "values": { "A": 5, "B": 2 } }
 */
class DefaultScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'default';
    }

    public function score(TestAttempt $attempt): array
    {
        $dimensions = [];
        $maxByDimension = [];

        $answers = $attempt->answers()->with('question')->get();

        foreach ($answers as $answer) {
            $rules = $answer->question->scoring ?? null;
            if (!$rules || !isset($rules['dimension'])) {
                continue;
            }

            $dim   = $rules['dimension'];
            $weight = (float) ($rules['weight'] ?? 1);
            $value  = $answer->value;

            $score = $this->extractScore($value, $rules);
            $dimensions[$dim] = ($dimensions[$dim] ?? 0) + ($score * $weight);

            $max = $rules['max'] ?? max($rules['values'] ?? [1]);
            $maxByDimension[$dim] = ($maxByDimension[$dim] ?? 0) + ($max * $weight);
        }

        // Normaliser sur 100
        $normalized = [];
        foreach ($dimensions as $dim => $raw) {
            $max = $maxByDimension[$dim] ?? 1;
            $normalized[$dim] = $max > 0 ? round(($raw / $max) * 100, 1) : 0;
        }

        return [
            'engine'     => $this->key(),
            'dimensions' => $normalized,
            'raw'        => $dimensions,
            'computed_at' => now()->toIso8601String(),
        ];
    }

    protected function extractScore(mixed $answer, array $rules): float
    {
        if (is_numeric($answer)) {
            return (float) $answer;
        }
        if (is_array($answer)) {
            $sum = 0;
            foreach ($answer as $a) {
                $sum += (float) ($rules['values'][$a] ?? 0);
            }
            return $sum;
        }
        return (float) ($rules['values'][$answer] ?? 0);
    }
}
