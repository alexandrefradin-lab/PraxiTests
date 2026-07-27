<?php

namespace Praxis\Plugins\PraxiCog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normes de référence PROVISOIRES pour PraxiCog.
 *
 * Unité : % de bonnes réponses (0-100), cohérente avec raw_scores du
 * PraxiCogScoringEngine. Ces valeurs sont des estimations de démarrage
 * (source : hypothèse interne) et servent uniquement à afficher un niveau
 * indicatif dès les premières passations.
 *
 * → Dès 50 passations, RecomputeNormsJob remplace ces valeurs par les normes
 *   réelles de la population PraxiQuest (computed_at renseigné). Ce seeder ne
 *   réécrase JAMAIS une norme auto-calculée.
 */
class NormsSeeder extends Seeder
{
    private const TEST_SLUG = 'praxicog-scoring';

    public function run(): void
    {
        $now = now();

        // mean/std_dev en % de réussite. Un test correctement calibré vise une
        // moyenne autour de 60 % avec une dispersion modérée.
        $norms = [
            'logique'   => ['mean' => 58, 'std_dev' => 20],
            'verbal'    => ['mean' => 66, 'std_dev' => 19],
            'numerique' => ['mean' => 60, 'std_dev' => 21],
            'spatial'   => ['mean' => 54, 'std_dev' => 22],
            'global'    => ['mean' => 60, 'std_dev' => 16],
        ];

        foreach ($norms as $dimension => $norm) {
            $existing = DB::table('test_norms')
                ->where('test_slug', self::TEST_SLUG)
                ->where('dimension', $dimension)
                ->where('group_key', 'all')
                ->first();

            $payload = [
                'n_responses' => 0,
                'mean'        => $norm['mean'],
                'std_dev'     => $norm['std_dev'],
                'source'      => 'Norme provisoire (hypothèse interne) — recalcul auto après 50 passations',
            ];

            if (!$existing) {
                DB::table('test_norms')->insert(array_merge($payload, [
                    'test_slug'   => self::TEST_SLUG,
                    'dimension'   => $dimension,
                    'group_key'   => 'all',
                    'computed_at' => null,   // null = norme publiée / provisoire (non auto-calculée)
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]));
            } elseif ($existing->computed_at === null) {
                // Ne met à jour que les normes provisoires, jamais les auto-calculées.
                DB::table('test_norms')->where('id', $existing->id)->update(array_merge($payload, [
                    'updated_at' => $now,
                ]));
            }
        }
    }
}
