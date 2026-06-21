<?php

namespace Praxis\Plugins\PraxiTempo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normes de référence pour PraxiTempo.
 *
 * Pas de normes publiées pour cet instrument maison : on insère des entrées
 * "vides" (mean / std_dev = null) par dimension. NormInterpreter affiche alors
 * les scores bruts sans étalonnage, et RecomputeNormsJob calcule les normes
 * automatiquement dès ~50 passations.
 *
 * Unité de raw_scores : moyenne des items sur l'échelle 1-5.
 */
class NormsSeeder extends Seeder
{
    private const TEST_SLUG = 'praxitempo-scoring';

    public function run(): void
    {
        $now  = now();
        $dims = ['priorisation', 'planification', 'focus', 'equilibre'];

        foreach ($dims as $dimension) {
            $existing = DB::table('test_norms')
                ->where('test_slug', self::TEST_SLUG)
                ->where('dimension', $dimension)
                ->where('group_key', 'all')
                ->first();

            if (! $existing) {
                DB::table('test_norms')->insert([
                    'test_slug'   => self::TEST_SLUG,
                    'dimension'   => $dimension,
                    'group_key'   => 'all',
                    'n_responses' => 0,
                    'mean'        => null,
                    'std_dev'     => null,
                    'source'      => 'Instrument maison Praxis — normes à calculer après ~50 passations.',
                    'computed_at' => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }
    }
}
