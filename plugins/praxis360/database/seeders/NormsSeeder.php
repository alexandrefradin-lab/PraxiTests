<?php

namespace Praxis\Plugins\Praxis360\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normes de référence pour l'étalonnage du test Praxis 360.
 *
 * Le référentiel d'origine (plugin WordPress maison) ne publie AUCUNE norme
 * statistique : pas de moyenne ni d'écart-type de population. Les valeurs sont
 * donc laissées à null. NormInterpreter affichera les scores bruts (moyennes
 * 1-5) sans étalonnage, jusqu'à ce que RecomputeNormsJob calcule des normes
 * automatiquement après 50 passations.
 *
 * Unités : le ScoringEngine retourne raw_scores en moyenne 1-5.
 */
class NormsSeeder extends Seeder
{
    /** Clé scoring engine — doit correspondre à ScoringEngine::key(). */
    private const TEST_SLUG = 'praxis360-softskills';

    public function run(): void
    {
        $now = now();

        // Une entrée par dimension déclarée dans Questions::dimensions().
        $dimensions = [
            'communication',
            'collaboration',
            'adaptabilite',
            'relation',
            'fiabilite',
            'leadership',
        ];

        foreach ($dimensions as $dimension) {
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
                    'n_responses' => 0,
                    'mean'        => null,   // Pas de norme publiée — à calculer
                    'std_dev'     => null,   // Pas de norme publiée — à calculer
                    'source'      => 'À calculer — auto-étalonnage après 50 passations',
                    'computed_at' => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
            // Si une entrée existe déjà (publiée ou auto-calculée), on ne l'écrase pas.
        }
    }
}
