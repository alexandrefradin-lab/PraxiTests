<?php

namespace Praxis\Plugins\PraxiSens\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normes de référence pour l'étalonnage du test PraxiSens (hypersensibilité).
 *
 * La HSPS originale (Aron & Aron, 1997) est cotée 1-7 ; PraxiSens utilise une
 * échelle 1-5 adaptée et des items en français. Aucune norme publiée n'existe
 * pour cette adaptation : on laisse donc mean/std_dev à null. NormInterpreter
 * affichera les scores normalisés sans étalonnage, et RecomputeNormsJob
 * calculera automatiquement les normes dès ~50 passations.
 *
 * Unités : raw_scores = moyenne des items sur l'échelle 1-5.
 */
class NormsSeeder extends Seeder
{
    private const TEST_SLUG = 'praxisens-sps';

    public function run(): void
    {
        $now = now();

        $dimensions = ['eoe', 'aes', 'lst'];
        $source = "À calculer (≥50 passations) — réf. Aron & Aron (1997) / Smolewska et al. (2006), échelle adaptée 1-5";

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
                    'mean'        => null,   // null = pas de norme publiée → auto-calcul
                    'std_dev'     => null,
                    'source'      => $source,
                    'computed_at' => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }
    }
}
