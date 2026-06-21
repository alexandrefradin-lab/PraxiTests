<?php

namespace Praxis\Plugins\PraxiFocus\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Normes de référence pour PraxiFocus (ASRS-v1.1).
 *
 * L'ASRS-v1.1 est un outil de REPÉRAGE à seuil (Partie A : ≥ 4 cases grisées),
 * et non une échelle étalonnée en percentiles par dimension. On ne seede donc
 * volontairement AUCUNE norme « mean / std_dev » publiée :
 *
 *   - Le résultat clinique s'appuie sur le screener Partie A (calculé par le
 *     ScoringEngine), pas sur une comparaison à une population.
 *   - Les barres de dimension affichent une fréquence descriptive (0-100),
 *     interprétée par bandes (peu fréquent / occasionnel / fréquent).
 *   - Si tu souhaites un étalonnage local, NormInterpreter calculera
 *     automatiquement des normes après 50 passations (RecomputeNormsJob).
 *
 * Ce seeder est donc volontairement un no-op idempotent (présent pour la
 * cohérence du pipeline d'activation des plugins).
 */
class NormsSeeder extends Seeder
{
    public function run(): void
    {
        // Aucune norme publiée seedée. Étalonnage local automatique après 50 passations.
    }
}
