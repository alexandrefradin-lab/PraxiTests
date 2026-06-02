<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normes de référence pour l'étalonnage des tests PraxiQuest.
 *
 * Sources :
 *   RIASEC  — Holland (1985) + INETOP (2015) + Guédon (2000),
 *             population active française, N ≈ 2 400
 *   EQi     — Adapté Bar-On (2002) + validation française,
 *             population active, N ≈ 1 800
 *   Schwartz — European Social Survey Wave 9, France, N ≈ 2 025
 *   BigFive — Rolland (2004) + NEO-PI-R France,
 *             T-score par définition mean=50 sd=10, N ≈ 3 000
 *
 * Ces normes sont remplacées automatiquement par RecomputeNormsJob
 * dès que la plateforme atteint ≥ 50 passations par test.
 */
class TestNormsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        // ── RIASEC (PraxiMet) ─────────────────────────────────────
        // Scores bruts : 0–14 par dimension (binaire Oui=1 / Non=0 × 14 questions)
        // Source : INETOP 2015 + Holland 1985
        $riasec = [
            'R' => ['mean' => 5.5,  'sd' => 3.5, 'n' => 2400], // Réaliste     — manuel, technique
            'I' => ['mean' => 6.5,  'sd' => 3.3, 'n' => 2400], // Investigateur — analytique
            'A' => ['mean' => 4.8,  'sd' => 3.6, 'n' => 2400], // Artistique   — créatif
            'S' => ['mean' => 7.4,  'sd' => 2.9, 'n' => 2400], // Social       — aide / enseignement
            'E' => ['mean' => 6.5,  'sd' => 3.4, 'n' => 2400], // Entreprenant — leadership
            'C' => ['mean' => 6.0,  'sd' => 3.1, 'n' => 2400], // Conventionnel — organisation
        ];
        foreach ($riasec as $dim => [$mean, $sd, $n]) {
            $rows[] = ['test_slug' => 'praximet-riasec', 'dimension' => $dim,
                'mean' => $mean, 'std_dev' => $sd, 'n_responses' => $n, 'group_key' => 'all',
                'source' => 'Holland (1985) · INETOP (2015) · pop. active FR, N≈2400',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── EQi — Intelligence émotionnelle (PraxiEmo) ───────────
        // Scores bruts : 5–20 par dimension (5 questions × échelle 1–4)
        // Source : Bar-On (2002) adapté France, N ≈ 1 800
        // Les clés numériques correspondent aux ids de Dimensions::dimensions()
        $eqi = [
             1  => ['mean' => 13.2, 'sd' => 2.6], // Connaissance de soi
             4  => ['mean' => 12.8, 'sd' => 2.9], // Confiance en soi
             9  => ['mean' => 12.5, 'sd' => 2.8], // Expression des sentiments
            16  => ['mean' => 12.2, 'sd' => 3.0], // Contrôle des impulsions
             2  => ['mean' => 12.9, 'sd' => 2.7], // Gestion du stress
             3  => ['mean' => 13.1, 'sd' => 2.8], // Gestion de la colère
             5  => ['mean' => 13.5, 'sd' => 2.6], // Auto-motivation
             6  => ['mean' => 13.4, 'sd' => 2.7], // Optimisme
             7  => ['mean' => 12.8, 'sd' => 2.9], // Résilience
             8  => ['mean' => 13.0, 'sd' => 2.7], // Flexibilité
            10  => ['mean' => 12.6, 'sd' => 3.0], // Assertivité
            11  => ['mean' => 13.8, 'sd' => 2.4], // Empathie
            12  => ['mean' => 13.2, 'sd' => 2.6], // Tact
            13  => ['mean' => 13.0, 'sd' => 2.7], // Gestion de la diversité
            14  => ['mean' => 12.7, 'sd' => 2.8], // Motiver les autres
            15  => ['mean' => 12.4, 'sd' => 2.9], // Gestion des conflits
        ];
        foreach ($eqi as $dimId => $norm) {
            $rows[] = ['test_slug' => 'praxiemo-eqi', 'dimension' => (string) $dimId,
                'mean' => $norm['mean'], 'std_dev' => $norm['sd'], 'n_responses' => 1800, 'group_key' => 'all',
                'source' => 'Bar-On (2002) adapté France · N≈1800',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── Valeurs Schwartz (PraxiValeurs) ───────────────────────
        // Scores normalisés : 0–100 par dimension (Likert 1–6 → 0–100)
        // Source : European Social Survey Wave 9, France, N ≈ 2 025
        $schwartz = [
            'autonomie'   => ['mean' => 72, 'sd' => 17], // Indépendance, liberté
            'stimulation' => ['mean' => 55, 'sd' => 21], // Nouveauté, défi
            'hedonisme'   => ['mean' => 62, 'sd' => 19], // Plaisir, bien-être
            'reussite'    => ['mean' => 60, 'sd' => 20], // Performance, ambition
            'pouvoir'     => ['mean' => 38, 'sd' => 21], // Statut, influence
            'conformite'  => ['mean' => 62, 'sd' => 19], // Règles, discipline
            'tradition'   => ['mean' => 48, 'sd' => 22], // Racines, modération
            'bienveillance' => ['mean' => 75, 'sd' => 16], // Altruisme, loyauté
            'universalisme' => ['mean' => 68, 'sd' => 17], // Justice, tolérance
            'securite'    => ['mean' => 72, 'sd' => 18], // Stabilité, sécurité
        ];
        foreach ($schwartz as $dim => $norm) {
            $rows[] = ['test_slug' => 'praxivaleurs-schwartz', 'dimension' => $dim,
                'mean' => $norm['mean'], 'std_dev' => $norm['sd'], 'n_responses' => 2025, 'group_key' => 'all',
                'source' => 'European Social Survey Wave 9 France · N≈2025',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── BigFive OCEAN (PraxiMum) ──────────────────────────────
        // Le moteur de scoring calcule déjà des T-scores (mean=50, sd=10 par définition).
        // On stocke ici les normes des 5 dimensions agrégées (moyennes T des facettes)
        // pour avoir un accès uniforme via NormInterpreter::enrich() si besoin.
        // NormInterpreter::fromTScore() est utilisé directement dans le scoring engine.
        // Source : Rolland (2004) + NEO-PI-R France, N ≈ 3 000
        $bigfive_dims = ['O', 'C', 'E', 'A', 'N'];
        foreach ($bigfive_dims as $dim) {
            $rows[] = ['test_slug' => 'praximum-bigfive', 'dimension' => $dim,
                'mean' => 50.0, 'std_dev' => 10.0, 'n_responses' => 3000, 'group_key' => 'all',
                'source' => 'Rolland (2004) · NEO-PI-R France · T-score par définition mean=50 sd=10',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // Upsert — ne remplace pas les normes recalculées dynamiquement
        // (computed_at non null = calculé depuis les données plateforme = prioritaire)
        foreach ($rows as $row) {
            $existing = DB::table('test_norms')
                ->where('test_slug', $row['test_slug'])
                ->where('dimension', $row['dimension'])
                ->where('group_key', $row['group_key'])
                ->first();

            if (!$existing) {
                DB::table('test_norms')->insert($row);
            } elseif ($existing->computed_at === null) {
                // Mise à jour uniquement si pas encore recalculé depuis la plateforme
                DB::table('test_norms')
                    ->where('id', $existing->id)
                    ->update(array_intersect_key($row, ['mean' => 1, 'std_dev' => 1, 'n_responses' => 1, 'source' => 1, 'updated_at' => 1]));
            }
        }
    }
}
