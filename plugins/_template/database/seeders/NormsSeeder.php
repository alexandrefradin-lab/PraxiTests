<?php

namespace Praxis\Plugins\PLUGIN_CLASS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normes de référence pour l'étalonnage du test PLUGIN_NAME.
 *
 * Ces valeurs permettent à NormInterpreter de convertir chaque score brut
 * en percentile, puis en label candidat (Très développé / Dans la moyenne…).
 *
 * Comment remplir ces valeurs :
 * ─────────────────────────────
 * Option A — Normes publiées : chercher dans la littérature scientifique
 *   du test (article de validation, manuel, ESS, etc.).
 *   → Fournir mean et std_dev dans les unités de score brut du ScoringEngine.
 *   → Indiquer la source et la taille de l'échantillon (n_responses).
 *
 * Option B — Pas de normes publiées : laisser mean et std_dev à null.
 *   → NormInterpreter affichera les scores bruts sans étalonnage.
 *   → Dès 50 passations, RecomputeNormsJob calculera les normes automatiquement.
 *
 * Unités :
 *   Le ScoringEngine retourne raw_scores en moyenne 1-4.
 *   Si mean et std_dev sont en moyenne 1-4, tout va bien.
 *   Si vous normalisez autrement, adapter ici.
 */
class NormsSeeder extends Seeder
{
    /** Clé scoring engine — doit correspondre à ScoringEngine::key(). */
    private const TEST_SLUG = 'PLUGIN_SLUG-scoring';

    public function run(): void
    {
        $now = now();

        // ── Normes par dimension ───────────────────────────────────────────
        // Format : dimension_key → [mean, std_dev, n_responses, source]
        //
        // IMPORTANT : mean et std_dev DOIVENT être dans les mêmes unités
        // que les valeurs retournées par raw_scores dans le ScoringEngine.
        // Par défaut : moyenne des items sur échelle 1-4.
        $norms = [
            'dim_alpha' => [
                'mean'      => 2.8,    // ← À remplacer par les vraies valeurs
                'std_dev'   => 0.6,    // ← À remplacer
                'n'         => 500,    // ← Taille de l'échantillon de référence
                'source'    => 'À renseigner — ex: NomAuteur (Année), population N=500',
            ],
            'dim_beta'  => [
                'mean'      => 2.5,
                'std_dev'   => 0.7,
                'n'         => 500,
                'source'    => 'À renseigner',
            ],
            // Ajouter une entrée par dimension déclarée dans Questions::dimensions()
        ];

        foreach ($norms as $dimension => $norm) {
            // Ne remplace PAS les normes déjà recalculées depuis la plateforme
            $existing = DB::table('test_norms')
                ->where('test_slug', self::TEST_SLUG)
                ->where('dimension', $dimension)
                ->where('group_key', 'all')
                ->first();

            if (!$existing) {
                DB::table('test_norms')->insert([
                    'test_slug'   => self::TEST_SLUG,
                    'dimension'   => $dimension,
                    'group_key'   => 'all',
                    'n_responses' => $norm['n'],
                    'mean'        => $norm['mean'],
                    'std_dev'     => $norm['std_dev'],
                    'source'      => $norm['source'],
                    'computed_at' => null, // null = norme publiée (pas auto-calculée)
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            } elseif ($existing->computed_at === null) {
                // Mise à jour des normes publiées (sans écraser les auto-calculées)
                DB::table('test_norms')->where('id', $existing->id)->update([
                    'mean'        => $norm['mean'],
                    'std_dev'     => $norm['std_dev'],
                    'n_responses' => $norm['n'],
                    'source'      => $norm['source'],
                    'updated_at'  => $now,
                ]);
            }
        }
    }
}
