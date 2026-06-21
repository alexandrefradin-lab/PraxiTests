<?php

namespace Praxis\Plugins\PraxiTempo\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PraxiTempo\Data\Questions;
use Carbon\Carbon;

/**
 * Scoring PraxiTempo — gestion du temps.
 *
 * Questionnaire d'auto-évaluation : 16 items "scale" (1-5), 4 par dimension.
 * Items inversés recodés 6 - valeur. Moyenne par dimension, normalisation 0-100
 * sur l'amplitude réelle 1-5, score global = moyenne des 4 dimensions (poids égaux).
 * Restitution typologique par archétype.
 */
class PraxiTempoScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxitempo-scoring';
    }

    /** @return array<string, float> dimension => poids (somme = 1.0) */
    public function dimensions(): array
    {
        return [
            'priorisation'  => 0.25,
            'planification' => 0.25,
            'focus'         => 0.25,
            'equilibre'     => 0.25,
        ];
    }

    public function score(TestAttempt $attempt): array
    {
        $weights = $this->dimensions();
        $sums    = array_fill_keys(array_keys($weights), 0.0);
        $counts  = array_fill_keys(array_keys($weights), 0);

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring  = $answer->question->scoring ?? [];
            $dim      = $scoring['dimension'] ?? null;
            $reversed = (bool) ($scoring['reversed'] ?? false);

            if (! $dim || ! isset($sums[$dim])) {
                continue;
            }

            // Le frontend émet 1..options.max (= 5), jamais 0.
            $val = max(1, min(5, (int) $answer->value));
            if ($reversed) {
                $val = 6 - $val;
            }

            $sums[$dim] += $val;
            $counts[$dim]++;
        }

        // Moyennes brutes (1-5) + scores normalisés (0-100).
        $rawScores  = [];
        $normalized = [];
        foreach (array_keys($weights) as $dim) {
            $avg = $counts[$dim] > 0 ? $sums[$dim] / $counts[$dim] : 1.0;
            $rawScores[$dim]  = round($avg, 2);
            $normalized[$dim] = (int) round((($avg - 1) / 4) * 100);
        }

        // Étalonnage (fallback silencieux tant qu'il n'y a pas de normes).
        $normScores = [];
        foreach ($rawScores as $dim => $raw) {
            $normScores[$dim] = NormInterpreter::enrich($this->key(), $dim, $raw);
        }

        // Score global pondéré (poids égaux → moyenne simple).
        $globalScore = 0.0;
        foreach ($weights as $dim => $w) {
            $globalScore += $normalized[$dim] * $w;
        }
        $globalScore = (int) round($globalScore);

        $archetype = $this->computeArchetype($normalized, $globalScore);
        $dims      = Questions::dimensions();

        return [
            'engine'       => $this->key(),
            'dimensions'   => $normalized,   // { priorisation: 72, ... } → ResultsShow
            'raw_scores'   => $rawScores,
            'norm_scores'  => $normScores,
            'global_score' => $globalScore,
            'meta'         => [
                'dimension_meta' => $this->dimensionMeta($dims),
                'dominant'       => $archetype['top_dimension'],
                'archetype'      => $archetype['key'],
                'archetype_label'=> $archetype['label'],
                'archetype_desc' => $archetype['description'],
                'interpretation' => $archetype['label'] . ' — ' . $archetype['description'],
                'strengths'      => array_keys(array_filter($normalized, fn ($s) => $s >= 70)),
                'growth_areas'   => array_keys(array_filter($normalized, fn ($s) => $s < 50)),
                'tips'           => array_map(fn ($d) => $d['tip'] ?? null, $dims),
            ],
            'computed_at'  => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string,int> $normalized
     * @return array{key:string,label:string,description:string,top_dimension:string}
     */
    private function computeArchetype(array $normalized, int $global): array
    {
        // Profil "subi" : l'urgent dicte les journées.
        if ($global < 40) {
            return [
                'key'           => 'pompier',
                'label'         => 'Le Pompier',
                'description'   => "Tu passes beaucoup de temps à éteindre des feux : l'urgent dicte tes journées, souvent au détriment de l'important. Bonne nouvelle — quelques routines de priorisation et de planification transformeraient radicalement ton quotidien.",
                'top_dimension' => $this->topDimension($normalized),
            ];
        }

        $top = $this->topDimension($normalized);

        $map = [
            'priorisation' => [
                'key'         => 'stratege',
                'label'       => 'Le Stratège',
                'description' => "Tu sais distinguer l'important de l'urgent et tu investis ton énergie là où elle compte vraiment. Ton défi : tenir ce cap quand la pression monte.",
            ],
            'planification' => [
                'key'         => 'architecte',
                'label'       => "L'Architecte",
                'description' => "Tu anticipes, tu structures, tu découpes : tes journées sont des plans qui tiennent. Veille simplement à garder de la souplesse face à l'imprévu.",
            ],
            'focus' => [
                'key'         => 'sprinteur',
                'label'       => 'Le Sprinteur',
                'description' => "Quand tu es lancé(e), rien ne t'arrête : concentration et exécution sont tes forces. Attention à ne pas confondre intensité et durabilité.",
            ],
            'equilibre' => [
                'key'         => 'equilibriste',
                'label'       => "L'Équilibriste",
                'description' => "Tu tiens la distance : pauses, énergie, charge soutenable — ton rythme est ton atout. Prochain palier : gagner en anticipation pour en faire moins, mais mieux.",
            ],
        ];

        $profile = $map[$top] ?? $map['priorisation'];
        $profile['top_dimension'] = $top;

        return $profile;
    }

    /** @param array<string,int> $normalized */
    private function topDimension(array $normalized): string
    {
        arsort($normalized);
        return (string) array_key_first($normalized);
    }

    /**
     * Libellés + descriptions des dimensions, consommés par ResultsShow.vue
     * (result.scoring.dimension_meta[key] = { label, description }).
     *
     * @param  array<string,array> $dims
     * @return array<string,array{label:string,description:string}>
     */
    private function dimensionMeta(array $dims): array
    {
        $meta = [];
        foreach ($dims as $key => $d) {
            $meta[$key] = [
                'label'       => $d['label'] ?? $key,
                'description' => $d['description'] ?? '',
            ];
        }
        return $meta;
    }
}
