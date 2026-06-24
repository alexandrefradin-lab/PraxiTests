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
        $minByDimension = [];

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

            // Contrat d'échelle : le front émet 1..max (jamais 0).
            // On normalise sur la plage réelle [min..max] pour que la réponse
            // minimale vaille 0 % et non min/max %.
            $hasValues = isset($rules['values']) && !empty($rules['values']);
            // Pour les questions multi-select, l'utilisateur peut cocher toutes les options
            // → le max théorique est la SOMME de toutes les valeurs, pas le max d'une seule.
            $isMulti = in_array($rules['type'] ?? '', ['multi', 'multiple'], true)
                || is_array($answer->value);
            $maxVal = $rules['max'] ?? ($hasValues
                ? ($isMulti ? array_sum(array_values($rules['values'])) : max($rules['values']))
                : 1);
            $minVal = $rules['min'] ?? ($hasValues ? min($rules['values']) : 1);
            $maxByDimension[$dim] = ($maxByDimension[$dim] ?? 0) + ($maxVal * $weight);
            $minByDimension[$dim] = ($minByDimension[$dim] ?? 0) + ($minVal * $weight);
        }

        // Normaliser sur 100 en centrant sur le minimum d'échelle, puis clamp 0..100.
        $normalized = [];
        foreach ($dimensions as $dim => $raw) {
            $max  = $maxByDimension[$dim] ?? 1;
            $min  = $minByDimension[$dim] ?? 0;
            $span = $max - $min;
            $pct  = $span > 0 ? round((($raw - $min) / $span) * 100, 1) : 0;
            $normalized[$dim] = max(0, min(100, $pct));
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
