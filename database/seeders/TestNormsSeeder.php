<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Barèmes de référence PROVISOIRES pour l'étalonnage des tests PraxiQuest.
 *
 * ⚠️ HONNÊTETÉ MÉTHODOLOGIQUE : ce sont des ESTIMATIONS INTERNES, non étalonnées
 * sur un échantillon de référence documenté. Les tests s'appuient sur des modèles
 * reconnus (RIASEC, intelligence émotionnelle, valeurs de Schwartz, Big Five/OCEAN),
 * mais ce sont des adaptations maison : les normes publiées de ces modèles ne sont
 * pas directement transférables et ne sont donc PAS reprises ici. `n_responses`
 * vaut 0 (aucun échantillon empirique de référence).
 *
 * Ces barèmes provisoires sont destinés à être remplacés par les vraies normes
 * plateforme (RecomputeNormsJob) dès que l'échantillon réel est suffisant.
 * Côté candidat, la restitution n'affiche que des labels qualitatifs (jamais de
 * percentile chiffré ni de citation) — cf. NormInterpreter (« règle d'or »).
 */
class TestNormsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        // ── RIASEC (PraxiMet) ─────────────────────────────────────
        // Scores bruts : 0–14 par dimension (binaire Oui=1 / Non=0 × 14 questions)
        // Barème provisoire (modèle RIASEC) — estimation interne, non étalonnée
        $riasec = [
            'R' => ['mean' => 5.5,  'sd' => 3.5, 'n' => 0], // Réaliste     — manuel, technique
            'I' => ['mean' => 6.5,  'sd' => 3.3, 'n' => 0], // Investigateur — analytique
            'A' => ['mean' => 4.8,  'sd' => 3.6, 'n' => 0], // Artistique   — créatif
            'S' => ['mean' => 7.4,  'sd' => 2.9, 'n' => 0], // Social       — aide / enseignement
            'E' => ['mean' => 6.5,  'sd' => 3.4, 'n' => 0], // Entreprenant — leadership
            'C' => ['mean' => 6.0,  'sd' => 3.1, 'n' => 0], // Conventionnel — organisation
        ];
        foreach ($riasec as $dim => ['mean' => $mean, 'sd' => $sd, 'n' => $n]) {
            $rows[] = ['test_slug' => 'praximet-riasec', 'dimension' => $dim,
                'mean' => $mean, 'std_dev' => $sd, 'n_responses' => $n, 'group_key' => 'all',
                'source' => 'Barème indicatif provisoire — modèle RIASEC, estimation interne non étalonnée',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── EQi — Intelligence émotionnelle (PraxiEmo) ───────────
        // Scores bruts : 5–20 par dimension (5 questions × échelle 1–4)
        // Barème provisoire (modèle d'intelligence émotionnelle) — estimation interne, non étalonnée
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
                'mean' => $norm['mean'], 'std_dev' => $norm['sd'], 'n_responses' => 0, 'group_key' => 'all',
                'source' => 'Barème indicatif provisoire — modèle intelligence émotionnelle, estimation interne non étalonnée',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── Valeurs Schwartz (PraxiValeurs) ───────────────────────
        // Scores normalisés : 0–100 par dimension (Likert 1–6 → 0–100)
        // Barème provisoire (valeurs de Schwartz) — estimation interne, non étalonnée
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
                'mean' => $norm['mean'], 'std_dev' => $norm['sd'], 'n_responses' => 0, 'group_key' => 'all',
                'source' => 'Barème indicatif provisoire — valeurs de Schwartz, estimation interne non étalonnée',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── BigFive OCEAN (PraxiMum) ──────────────────────────────
        // Le moteur de scoring calcule déjà des T-scores (mean=50, sd=10 par définition).
        // On stocke ici les normes des 5 dimensions agrégées (moyennes T des facettes)
        // pour avoir un accès uniforme via NormInterpreter::enrich() si besoin.
        // NormInterpreter::fromTScore() est utilisé directement dans le scoring engine.
        // T-score standard (mean=50, sd=10 par définition) — barème provisoire, non étalonné
        $bigfive_dims = ['O', 'C', 'E', 'A', 'N'];
        foreach ($bigfive_dims as $dim) {
            $rows[] = ['test_slug' => 'praximum-bigfive', 'dimension' => $dim,
                'mean' => 50.0, 'std_dev' => 10.0, 'n_responses' => 0, 'group_key' => 'all',
                'source' => 'T-score standard (mean=50 sd=10) — barème indicatif provisoire, estimation interne non étalonnée',
                'computed_at' => null, 'created_at' => $now, 'updated_at' => $now];
        }

        // Batch upsert — ne remplace pas les normes recalculées dynamiquement
        // (computed_at non null = calculé depuis les données plateforme = prioritaire).
        // On sépare les lignes à insérer de celles à mettre à jour pour éviter
        // d'écraser computed_at sur les enregistrements déjà calculés.
        $existing = DB::table('test_norms')
            ->select(['id', 'test_slug', 'dimension', 'group_key', 'computed_at'])
            ->get()
            ->keyBy(fn ($r) => $r->test_slug . '|' . $r->dimension . '|' . $r->group_key);

        $toInsert = [];
        $toUpdate = []; // [id => [...fields]]

        foreach ($rows as $row) {
            $key = $row['test_slug'] . '|' . $row['dimension'] . '|' . $row['group_key'];
            if (!isset($existing[$key])) {
                $toInsert[] = $row;
            } elseif ($existing[$key]->computed_at === null) {
                $toUpdate[$existing[$key]->id] = array_intersect_key(
                    $row,
                    array_flip(['mean', 'std_dev', 'n_responses', 'source', 'updated_at'])
                );
            }
            // Si computed_at non null : données plateforme prioritaires, on ne touche pas
        }

        if (!empty($toInsert)) {
            DB::table('test_norms')->insert($toInsert);
        }

        foreach ($toUpdate as $id => $fields) {
            DB::table('test_norms')->where('id', $id)->update($fields);
        }
    }
}
