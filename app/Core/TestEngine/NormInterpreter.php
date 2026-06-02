<?php

namespace Praxis\Core\TestEngine;

/**
 * Moteur d'étalonnage — convertit des scores bruts en percentiles
 * et en labels compréhensibles sans jargon psychométrique.
 *
 * Règle d'or côté candidat : jamais le mot "percentile" ni de
 * chiffres statistiques — uniquement des labels descriptifs.
 */
class NormInterpreter
{
    /** Cache in-request pour éviter N requêtes DB. */
    private static array $cache = [];

    // ─── Labels candidat (5 niveaux) ──────────────────────────────

    /**
     * Convertit un percentile (1-99) en label lisible.
     *
     * Les libellés sont volontairement neutres et positifs — on ne
     * dit jamais "faible" ou "mauvais", mais "peu présent" ou "en
     * développement", ce qui laisse la porte ouverte à la progression.
     */
    public static function label(int $percentile): array
    {
        return match (true) {
            $percentile >= 85 => [
                'level'       => 5,
                'label'       => 'Très développé',
                'description' => 'Cette dimension est particulièrement forte dans votre profil.',
                'dots'        => 5,
                'color'       => 'gold',
            ],
            $percentile >= 65 => [
                'level'       => 4,
                'label'       => 'Au-dessus de la moyenne',
                'description' => 'Cette dimension est plus développée que chez la plupart des personnes.',
                'dots'        => 4,
                'color'       => 'navy',
            ],
            $percentile >= 35 => [
                'level'       => 3,
                'label'       => 'Dans la moyenne',
                'description' => 'Cette dimension est similaire à celle de la plupart des personnes.',
                'dots'        => 3,
                'color'       => 'slate',
            ],
            $percentile >= 15 => [
                'level'       => 2,
                'label'       => 'En développement',
                'description' => 'Cette dimension est moins présente, mais peut être renforcée.',
                'dots'        => 2,
                'color'       => 'amber',
            ],
            default => [
                'level'       => 1,
                'label'       => 'Peu présent',
                'description' => 'Cette dimension est peu marquée dans votre profil actuel.',
                'dots'        => 1,
                'color'       => 'muted',
            ],
        };
    }

    // ─── Enrichissement depuis la table test_norms ─────────────────

    /**
     * Cherche les normes en DB et enrichit le score brut.
     * Retourne null sur label/percentile si aucune norme trouvée
     * (le score brut reste disponible pour affichage de fallback).
     */
    public static function enrich(
        string $testSlug,
        string $dimension,
        float  $rawScore,
        string $group = 'all',
    ): array {
        $norm = static::getNorm($testSlug, $dimension, $group);

        if (!$norm || $norm['std_dev'] <= 0) {
            return static::fallback($rawScore);
        }

        $z   = ($rawScore - $norm['mean']) / $norm['std_dev'];
        $pct = max(1, min(99, (int) round(static::normalCdf($z) * 100)));

        return array_merge(
            ['score' => $rawScore, 'percentile' => $pct, 'n_ref' => $norm['n']],
            static::label($pct),
        );
    }

    /**
     * Calcule le percentile directement depuis un T-score (BigFive).
     * T-score : mean=50, sd=10 par définition.
     *   T=60 → ~84ème percentile
     *   T=70 → ~98ème percentile
     *   T=40 → ~16ème percentile
     */
    public static function fromTScore(int $tScore): array
    {
        $z   = ($tScore - 50) / 10.0;
        $pct = max(1, min(99, (int) round(static::normalCdf($z) * 100)));

        return array_merge(
            ['score' => $tScore, 'percentile' => $pct, 'n_ref' => null],
            static::label($pct),
        );
    }

    // ─── Recalcul dynamique depuis les données plateforme ──────────

    /**
     * Recalcule et met à jour les normes pour un test+dimension
     * à partir des résultats stockés dans test_results.
     * Appelé par RecomputeNormsJob (schedule hebdomadaire).
     *
     * Minimum 50 passations avant de remplacer les normes publiées.
     */
    public static function recompute(string $testSlug, string $dimension, int $minSample = 50): bool
    {
        try {
            $scores = \DB::table('test_results')
                ->join('test_attempts', 'test_results.attempt_id', '=', 'test_attempts.id')
                ->join('tests', 'test_attempts.test_id', '=', 'tests.id')
                ->where('tests.slug', $testSlug)
                ->whereNotNull('test_results.scoring')
                ->pluck('test_results.scoring')
                ->map(fn($s) => is_string($s) ? json_decode($s, true) : $s)
                ->map(fn($s) => static::extractScore($s, $dimension))
                ->filter(fn($v) => $v !== null)
                ->values();

            if ($scores->count() < $minSample) return false;

            $mean   = $scores->average();
            $stdDev = sqrt($scores->map(fn($v) => pow($v - $mean, 2))->average());

            \DB::table('test_norms')->updateOrInsert(
                ['test_slug' => $testSlug, 'dimension' => $dimension, 'group_key' => 'all'],
                [
                    'n_responses' => $scores->count(),
                    'mean'        => round($mean, 4),
                    'std_dev'     => round($stdDev, 4),
                    'source'      => 'Platform users — auto-computed',
                    'computed_at' => now(),
                    'updated_at'  => now(),
                ],
            );

            // Vider le cache
            static::$cache = [];
            return true;

        } catch (\Throwable $e) {
            logger()->warning("NormInterpreter::recompute failed [{$testSlug}/{$dimension}]: {$e->getMessage()}");
            return false;
        }
    }

    // ─── Internals ─────────────────────────────────────────────────

    private static function getNorm(string $testSlug, string $dimension, string $group): ?array
    {
        $key = "{$testSlug}:{$dimension}:{$group}";

        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        try {
            $row = \DB::table('test_norms')
                ->where('test_slug', $testSlug)
                ->where('dimension', $dimension)
                ->where('group_key', $group)
                ->first();

            return static::$cache[$key] = $row ? [
                'mean'    => (float) $row->mean,
                'std_dev' => (float) $row->std_dev,
                'n'       => (int) $row->n_responses,
            ] : null;

        } catch (\Throwable) {
            return static::$cache[$key] = null;
        }
    }

    private static function fallback(float $score): array
    {
        return [
            'score'       => $score,
            'percentile'  => null,
            'label'       => null,
            'description' => null,
            'level'       => null,
            'dots'        => null,
            'color'       => null,
            'n_ref'       => null,
        ];
    }

    /** Tente d'extraire un score brut d'une dimension depuis le JSON scoring. */
    private static function extractScore(array $scoring, string $dimension): ?float
    {
        // RIASEC raw_scores
        if (isset($scoring['raw_scores'][$dimension])) {
            return (float) $scoring['raw_scores'][$dimension];
        }
        // EQi dim_scores
        if (isset($scoring['dim_scores'][$dimension])) {
            return (float) $scoring['dim_scores'][$dimension];
        }
        // Schwartz dimensions
        if (isset($scoring['dimensions'][$dimension])) {
            return (float) $scoring['dimensions'][$dimension];
        }
        return null;
    }

    /**
     * CDF de la loi normale — approximation Abramowitz & Stegun 26.2.17.
     * Erreur max < 7.5 × 10⁻⁸, largement suffisant pour des percentiles.
     */
    private static function normalCdf(float $z): float
    {
        $b   = [0.319381530, -0.356563782, 1.781477937, -1.821255978, 1.330274429];
        $t   = 1.0 / (1.0 + 0.2316419 * abs($z));
        $pol = $t * ($b[0] + $t * ($b[1] + $t * ($b[2] + $t * ($b[3] + $t * $b[4]))));
        $pdf = exp(-0.5 * $z * $z) / sqrt(2.0 * M_PI);
        $cdf = 1.0 - $pdf * $pol;
        return $z >= 0 ? $cdf : 1.0 - $cdf;
    }
}
