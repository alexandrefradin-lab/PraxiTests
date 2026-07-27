<?php

namespace Praxis\Core\TestEngine;

/**
 * Moteur d'étalonnage — convertit des scores bruts en percentiles
 * et en labels compréhensibles sans jargon psychométrique.
 *
 * Règle d'or côté candidat : jamais le mot "percentile" ni de
 * chiffres statistiques — uniquement des labels descriptifs.
 *
 * ── Auto-étalonnage ──────────────────────────────────────────────
 * Deux origines de normes coexistent dans test_norms :
 *   'reference' — littérature scientifique ou provisoires (seedées),
 *                 jamais modifiées par le recalcul automatique ;
 *   'platform'  — recalculées chaque semaine depuis les passations
 *                 réelles (recomputeAll), globales et par tranche d'âge.
 *
 * Chaîne de résolution à la lecture (enrich) :
 *   1. norme plateforme du groupe d'âge du candidat  (n ≥ 200)
 *   2. norme plateforme globale                       (n ≥ 200)
 *   3. mélange pondéré référence + plateforme         (50 ≤ n < 200)
 *   4. norme de référence seule
 *   5. score brut sans étalonnage (fallback)
 *
 * Le percentile est calculé UNE FOIS au scoring et persisté dans le
 * JSON `norm_scores` : les rapports déjà générés ne bougent jamais,
 * même quand les normes évoluent. La provenance (norm_origin,
 * norm_group, n_ref) est stockée avec chaque score pour l'audit.
 */
class NormInterpreter
{
    /** Passations minimum pour STOCKER une norme plateforme. */
    public const MIN_STORE = 50;

    /** Passations minimum pour qu'une norme plateforme soit utilisée SEULE. */
    public const MIN_USE = 200;

    /** Cache in-request pour éviter N requêtes DB. */
    private static array $cache = [];

    /** Groupe d'étalonnage du candidat en cours de scoring ('age:25-34'…). */
    private static ?string $candidateGroup = null;

    // ─── Contexte candidat (posé par TestEngine avant score()) ─────

    public static function setCandidateGroup(?string $group): void
    {
        static::$candidateGroup = $group;
    }

    public static function clearCandidateGroup(): void
    {
        static::$candidateGroup = null;
    }

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
     * Cherche la meilleure norme disponible (voir chaîne de résolution
     * en tête de classe) et enrichit le score brut.
     * Retourne null sur label/percentile si aucune norme trouvée
     * (le score brut reste disponible pour affichage de fallback).
     *
     * $group : groupe d'étalonnage explicite ; par défaut celui du
     * candidat en cours de scoring (setCandidateGroup), sinon aucun.
     */
    public static function enrich(
        string  $testSlug,
        string  $dimension,
        float   $rawScore,
        ?string $group = null,
    ): array {
        $group ??= static::$candidateGroup;

        [$norm, $origin, $usedGroup] = static::resolveNorm($testSlug, $dimension, $group);

        if (!$norm || $norm['std_dev'] <= 0) {
            return static::fallback($rawScore);
        }

        $z   = ($rawScore - $norm['mean']) / $norm['std_dev'];
        $pct = max(1, min(99, (int) round(static::normalCdf($z) * 100)));

        return array_merge(
            [
                'score'       => $rawScore,
                'percentile'  => $pct,
                'n_ref'       => $norm['n'],
                'norm_origin' => $origin,
                'norm_group'  => $usedGroup,
            ],
            static::label($pct),
        );
    }

    /**
     * Chaîne de résolution : [norme, origine effective, groupe utilisé].
     * Origine effective : 'platform', 'blend' ou 'reference'.
     */
    private static function resolveNorm(string $testSlug, string $dimension, ?string $group): array
    {
        // 1. Norme plateforme du groupe d'âge, si assez de passations.
        if ($group !== null && $group !== 'all') {
            $grpNorm = static::getNorm($testSlug, $dimension, $group, 'platform');
            if ($grpNorm && $grpNorm['n'] >= static::MIN_USE && $grpNorm['std_dev'] > 0) {
                return [$grpNorm, 'platform', $group];
            }
        }

        $platform  = static::getNorm($testSlug, $dimension, 'all', 'platform');
        $reference = static::getNorm($testSlug, $dimension, 'all', 'reference');

        // 2. Norme plateforme globale seule.
        if ($platform && $platform['n'] >= static::MIN_USE && $platform['std_dev'] > 0) {
            return [$platform, 'platform', 'all'];
        }

        // 3. Zone de transition : mélange pondéré par les effectifs.
        if ($platform && $platform['n'] >= static::MIN_STORE && $platform['std_dev'] > 0
            && $reference && $reference['std_dev'] > 0) {
            return [static::blend($reference, $platform), 'blend', 'all'];
        }

        // 4. Référence scientifique / provisoire.
        if ($reference) {
            return [$reference, 'reference', 'all'];
        }

        return [null, null, null];
    }

    /**
     * Mélange pondéré référence + plateforme. Le n de référence est plafonné
     * à MIN_USE pour que les données réelles prennent progressivement le
     * dessus même face à un N de littérature très élevé (ex. 2 400).
     */
    private static function blend(array $reference, array $platform): array
    {
        $wr = min(max($reference['n'], 1), static::MIN_USE);
        $wp = $platform['n'];
        $w  = $wr + $wp;

        return [
            'mean'    => ($wr * $reference['mean'] + $wp * $platform['mean']) / $w,
            'std_dev' => ($wr * $reference['std_dev'] + $wp * $platform['std_dev']) / $w,
            'n'       => $w,
        ];
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
     * Recalcule les normes plateforme de TOUS les tests ayant des résultats.
     * Appelé par le schedule hebdomadaire (routes/console.php).
     *
     * Le slug de norme est tests.scoring_engine : c'est la clé que chaque
     * moteur passe à enrich() (key() du moteur). Les tests sur moteur
     * 'default' n'appellent pas enrich() et sont ignorés d'office (aucune
     * dimension découverte dans norm_scores).
     *
     * @return array<string,int> slug de norme => nombre de normes écrites
     */
    public static function recomputeAll(int $minSample = self::MIN_STORE): array
    {
        $written = [];

        try {
            $tests = \App\Models\Test::query()->whereNotNull('scoring_engine')->get();
        } catch (\Throwable $e) {
            logger()->warning("NormInterpreter::recomputeAll: {$e->getMessage()}");
            return $written;
        }

        foreach ($tests as $test) {
            $count = static::recomputeForTest($test, $minSample);
            if ($count > 0) {
                $written[$test->scoring_engine] = ($written[$test->scoring_engine] ?? 0) + $count;
            }
        }

        return $written;
    }

    /**
     * Recalcule les normes plateforme d'un test : globales ('all') et par
     * tranche d'âge ('age:<band>') quand l'effectif du groupe suffit.
     *
     * N'écrit QUE des lignes origin='platform' — les normes de référence
     * seedées ne sont jamais touchées.
     *
     * @return int Nombre de normes écrites/actualisées.
     */
    public static function recomputeForTest(\App\Models\Test $test, int $minSample = self::MIN_STORE): int
    {
        try {
            $rows = \DB::table('test_results')
                ->join('test_attempts', 'test_results.attempt_id', '=', 'test_attempts.id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'test_attempts.user_id')
                ->where('test_attempts.test_id', $test->id)
                ->whereNotNull('test_results.scoring')
                ->get(['test_results.scoring', 'profiles.age_band']);

            if ($rows->count() < $minSample) {
                return 0;
            }

            $normSlug = $test->scoring_engine ?: $test->slug;

            // Regrouper les scores par dimension puis par groupe d'étalonnage.
            // Dimensions découvertes depuis les norm_scores persistés — donc
            // exactement celles que le moteur étalonne réellement.
            $byDim = [];
            foreach ($rows as $row) {
                $scoring = is_string($row->scoring) ? json_decode($row->scoring, true) : (array) $row->scoring;
                if (!is_array($scoring)) {
                    continue;
                }

                $dims = array_keys(is_array($scoring['norm_scores'] ?? null) ? $scoring['norm_scores'] : []);
                foreach ($dims as $dim) {
                    $value = static::extractScore($scoring, (string) $dim);
                    if ($value === null) {
                        continue;
                    }
                    $byDim[$dim]['all'][] = $value;
                    if ($row->age_band) {
                        $byDim[$dim]["age:{$row->age_band}"][] = $value;
                    }
                }
            }

            $written = 0;
            foreach ($byDim as $dim => $groups) {
                foreach ($groups as $groupKey => $scores) {
                    if (count($scores) < $minSample) {
                        continue;
                    }

                    $n      = count($scores);
                    $mean   = array_sum($scores) / $n;
                    $stdDev = sqrt(array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $scores)) / $n);

                    \DB::table('test_norms')->updateOrInsert(
                        [
                            'test_slug' => $normSlug,
                            'dimension' => (string) $dim,
                            'group_key' => $groupKey,
                            'origin'    => 'platform',
                        ],
                        [
                            'n_responses' => $n,
                            'mean'        => round($mean, 4),
                            'std_dev'     => round($stdDev, 4),
                            'source'      => "Passations plateforme (auto) — {$groupKey}",
                            'computed_at' => now(),
                            'updated_at'  => now(),
                            'created_at'  => now(),
                        ],
                    );
                    $written++;
                }
            }

            if ($written > 0) {
                static::$cache = [];
            }

            return $written;

        } catch (\Throwable $e) {
            logger()->warning("NormInterpreter::recomputeForTest failed [test #{$test->id}]: {$e->getMessage()}");
            return 0;
        }
    }

    // ─── Internals ─────────────────────────────────────────────────

    private static function getNorm(string $testSlug, string $dimension, string $group, string $origin): ?array
    {
        $key = "{$testSlug}:{$dimension}:{$group}:{$origin}";

        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        try {
            $row = \DB::table('test_norms')
                ->where('test_slug', $testSlug)
                ->where('dimension', $dimension)
                ->where('group_key', $group)
                ->where('origin', $origin)
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
            'norm_origin' => null,
            'norm_group'  => null,
        ];
    }

    /** Tente d'extraire un score brut d'une dimension depuis le JSON scoring. */
    private static function extractScore(array $scoring, string $dimension): ?float
    {
        // Universel : tous les moteurs qui appellent enrich()/fromTScore()
        // persistent le score brut dans norm_scores[dim]['score'].
        if (isset($scoring['norm_scores'][$dimension]['score'])
            && is_numeric($scoring['norm_scores'][$dimension]['score'])) {
            return (float) $scoring['norm_scores'][$dimension]['score'];
        }
        // RIASEC raw_scores
        if (isset($scoring['raw_scores'][$dimension]) && is_numeric($scoring['raw_scores'][$dimension])) {
            return (float) $scoring['raw_scores'][$dimension];
        }
        // EQi dim_scores
        if (isset($scoring['dim_scores'][$dimension]) && is_numeric($scoring['dim_scores'][$dimension])) {
            return (float) $scoring['dim_scores'][$dimension];
        }
        // Schwartz dimensions
        if (isset($scoring['dimensions'][$dimension]) && is_numeric($scoring['dimensions'][$dimension])) {
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
