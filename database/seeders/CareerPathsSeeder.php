<?php

namespace Database\Seeders;

use App\Models\CareerPath;
use Illuminate\Database\Seeder;

/**
 * Référentiel PTP — niveau 1 (estimations par famille, saisies à la main).
 *
 * formation_months / market_* sont des ESTIMATIONS de démarrage. Elles gagneront
 * en rigueur en Lot 2 (RNCP + France Travail via code ROME) sans changer l'algo.
 * Idempotent : ré-exécutable, clé = slug.
 *
 * fit_dimensions = lettres RIASEC (cf. test praximet) qui nourrissent le fit.
 */
class CareerPathsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->paths() as $p) {
            CareerPath::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }

    /** @return array<int,array<string,mixed>> */
    protected function paths(): array
    {
        $eur = fn (int $min, int $max, int $median) => [
            'min' => $min, 'max' => $max, 'median' => $median, 'currency' => 'EUR',
        ];

        return [
            ['slug' => 'developpeur-web', 'title' => 'Développeur web', 'family' => 'Numérique', 'formation_months' => 9, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['I', 'C'], 'salary_indicative' => $eur(28000, 48000, 36000)],
            ['slug' => 'data-analyst', 'title' => 'Data analyst', 'family' => 'Numérique', 'formation_months' => 12, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['I', 'C'], 'salary_indicative' => $eur(32000, 55000, 42000)],
            ['slug' => 'ux-designer', 'title' => 'UX designer', 'family' => 'Numérique', 'formation_months' => 8, 'market_demand' => 'moyen', 'market_trend' => 'croissance', 'fit_dimensions' => ['A', 'I'], 'salary_indicative' => $eur(30000, 50000, 38000)],
            ['slug' => 'chef-projet-digital', 'title' => 'Chef de projet digital', 'family' => 'Numérique', 'formation_months' => 6, 'market_demand' => 'moyen', 'market_trend' => 'croissance', 'fit_dimensions' => ['E', 'C'], 'salary_indicative' => $eur(34000, 55000, 43000)],
            ['slug' => 'community-manager', 'title' => 'Community manager', 'family' => 'Communication', 'formation_months' => 4, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['A', 'E'], 'salary_indicative' => $eur(26000, 40000, 32000)],
            ['slug' => 'conseiller-insertion', 'title' => 'Conseiller en insertion professionnelle', 'family' => 'Social', 'formation_months' => 10, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['S', 'E'], 'salary_indicative' => $eur(25000, 38000, 30000)],
            ['slug' => 'formateur-adultes', 'title' => 'Formateur pour adultes', 'family' => 'Formation', 'formation_months' => 6, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['S', 'A'], 'salary_indicative' => $eur(26000, 42000, 33000)],
            ['slug' => 'coach-professionnel', 'title' => 'Coach professionnel', 'family' => 'Accompagnement', 'formation_months' => 9, 'market_demand' => 'moyen', 'market_trend' => 'croissance', 'fit_dimensions' => ['S', 'E'], 'salary_indicative' => $eur(28000, 60000, 38000)],
            ['slug' => 'mediateur-social', 'title' => 'Médiateur social', 'family' => 'Social', 'formation_months' => 8, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['S'], 'salary_indicative' => $eur(24000, 34000, 28000)],
            ['slug' => 'assistant-rh', 'title' => 'Assistant RH', 'family' => 'Ressources humaines', 'formation_months' => 6, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['C', 'S'], 'salary_indicative' => $eur(26000, 38000, 31000)],
            ['slug' => 'gestionnaire-paie', 'title' => 'Gestionnaire de paie', 'family' => 'Ressources humaines', 'formation_months' => 9, 'market_demand' => 'fort', 'market_trend' => 'stable', 'fit_dimensions' => ['C'], 'salary_indicative' => $eur(30000, 45000, 36000)],
            ['slug' => 'comptable', 'title' => 'Comptable', 'family' => 'Gestion', 'formation_months' => 12, 'market_demand' => 'fort', 'market_trend' => 'stable', 'fit_dimensions' => ['C'], 'salary_indicative' => $eur(28000, 46000, 35000)],
            ['slug' => 'secretaire-medical', 'title' => 'Secrétaire médical(e)', 'family' => 'Santé', 'formation_months' => 5, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['C', 'S'], 'salary_indicative' => $eur(22000, 30000, 26000)],
            ['slug' => 'aide-soignant', 'title' => 'Aide-soignant(e)', 'family' => 'Santé', 'formation_months' => 11, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['S', 'R'], 'salary_indicative' => $eur(22000, 30000, 25000)],
            ['slug' => 'infirmier', 'title' => 'Infirmier(e)', 'family' => 'Santé', 'formation_months' => 24, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['S', 'I'], 'salary_indicative' => $eur(28000, 42000, 33000)],
            ['slug' => 'educateur-specialise', 'title' => 'Éducateur spécialisé', 'family' => 'Social', 'formation_months' => 18, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['S'], 'salary_indicative' => $eur(25000, 36000, 29000)],
            ['slug' => 'electricien', 'title' => 'Électricien(ne)', 'family' => 'Bâtiment', 'formation_months' => 10, 'market_demand' => 'fort', 'market_trend' => 'stable', 'fit_dimensions' => ['R'], 'salary_indicative' => $eur(24000, 38000, 30000)],
            ['slug' => 'plombier-chauffagiste', 'title' => 'Plombier-chauffagiste', 'family' => 'Bâtiment', 'formation_months' => 10, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['R'], 'salary_indicative' => $eur(24000, 40000, 31000)],
            ['slug' => 'menuisier', 'title' => 'Menuisier', 'family' => 'Artisanat', 'formation_months' => 12, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['R', 'A'], 'salary_indicative' => $eur(22000, 36000, 28000)],
            ['slug' => 'cuisinier', 'title' => 'Cuisinier', 'family' => 'Restauration', 'formation_months' => 8, 'market_demand' => 'fort', 'market_trend' => 'stable', 'fit_dimensions' => ['R', 'A'], 'salary_indicative' => $eur(22000, 35000, 27000)],
            ['slug' => 'boulanger', 'title' => 'Boulanger', 'family' => 'Artisanat', 'formation_months' => 8, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['R'], 'salary_indicative' => $eur(21000, 32000, 25000)],
            ['slug' => 'conducteur-transport', 'title' => 'Conducteur de transport en commun', 'family' => 'Transport', 'formation_months' => 3, 'market_demand' => 'fort', 'market_trend' => 'stable', 'fit_dimensions' => ['R', 'C'], 'salary_indicative' => $eur(23000, 32000, 27000)],
            ['slug' => 'technicien-maintenance', 'title' => 'Technicien de maintenance', 'family' => 'Industrie', 'formation_months' => 12, 'market_demand' => 'fort', 'market_trend' => 'croissance', 'fit_dimensions' => ['R', 'I'], 'salary_indicative' => $eur(26000, 40000, 32000)],
            ['slug' => 'agent-immobilier', 'title' => 'Agent immobilier', 'family' => 'Commerce', 'formation_months' => 4, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['E', 'S'], 'salary_indicative' => $eur(24000, 60000, 34000)],
            ['slug' => 'commercial-b2b', 'title' => 'Commercial B2B', 'family' => 'Commerce', 'formation_months' => 3, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['E'], 'salary_indicative' => $eur(28000, 65000, 40000)],
            ['slug' => 'charge-communication', 'title' => 'Chargé de communication', 'family' => 'Communication', 'formation_months' => 9, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['A', 'E'], 'salary_indicative' => $eur(28000, 44000, 34000)],
            ['slug' => 'graphiste', 'title' => 'Graphiste', 'family' => 'Design', 'formation_months' => 9, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['A'], 'salary_indicative' => $eur(24000, 40000, 30000)],
            ['slug' => 'assistant-administratif', 'title' => 'Assistant administratif', 'family' => 'Administration', 'formation_months' => 0, 'market_demand' => 'moyen', 'market_trend' => 'declin', 'fit_dimensions' => ['C'], 'salary_indicative' => $eur(22000, 32000, 26000)],
            ['slug' => 'teleconseiller', 'title' => 'Téléconseiller', 'family' => 'Relation client', 'formation_months' => 0, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['S', 'C'], 'salary_indicative' => $eur(21000, 30000, 24000)],
            ['slug' => 'manager-proximite', 'title' => 'Manager de proximité', 'family' => 'Management', 'formation_months' => 6, 'market_demand' => 'moyen', 'market_trend' => 'stable', 'fit_dimensions' => ['E', 'S'], 'salary_indicative' => $eur(30000, 50000, 38000)],
        ];
    }
}
