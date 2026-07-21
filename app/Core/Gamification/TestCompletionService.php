<?php

namespace Praxis\Core\Gamification;

use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;

/**
 * Avancement du candidat dans L'Armurerie (les Épreuves).
 *
 * Sert de PORTE D'ENTRÉE à La Salle du Trésor : aucune mini-app ne peut être
 * ouverte tant que toutes les Épreuves ne sont pas passées.
 *
 * Le périmètre « toutes les Épreuves » est exactement celui affiché dans
 * L'Armurerie : les tests publiés, moins ceux qui sont eux-mêmes des cadeaux
 * de la Salle du Trésor (sinon la porte exigerait d'avoir déjà franchi la porte).
 */
class TestCompletionService
{
    /**
     * @return array{completed:int,total:int,all_done:bool,remaining:int}
     */
    public function summary(User $user): array
    {
        $required = $this->requiredTestIds();
        $total    = count($required);

        if ($total === 0) {
            // Aucune Épreuve publiée : la porte reste fermée plutôt que de
            // s'ouvrir gratuitement sur une base vide (installation neuve, seeders
            // non passés, catalogue en cours de configuration).
            return ['completed' => 0, 'total' => 0, 'all_done' => false, 'remaining' => 0];
        }

        $completed = TestAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereIn('test_id', $required)
            ->distinct()
            ->count('test_id');

        return [
            'completed' => $completed,
            'total'     => $total,
            'all_done'  => $completed >= $total,
            'remaining' => max(0, $total - $completed),
        ];
    }

    public function hasCompletedAll(User $user): bool
    {
        return $this->summary($user)['all_done'];
    }

    /**
     * IDs des Épreuves qui comptent pour la porte d'entrée.
     * RewardCatalog est résolu à l'appel (et non injecté) pour éviter un cycle :
     * RewardCatalog dépend de ce service pour exposer l'état de la porte.
     */
    protected function requiredTestIds(): array
    {
        $rewardSlugs = app(RewardCatalog::class)->testSlugs();

        return Test::where('published', true)
            ->when($rewardSlugs, fn ($q) => $q->whereNotIn('slug', $rewardSlugs))
            ->pluck('id')
            ->all();
    }
}
