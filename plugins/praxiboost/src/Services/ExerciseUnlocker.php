<?php

namespace Praxis\Plugins\PraxiBoost\Services;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Plugins\PraxiBoost\Models\DevExercise;
use Praxis\Plugins\PraxiBoost\Models\DevExerciseProgress;

/**
 * Déblocage par paliers (accumulation, sans dépense).
 *
 * Un exercice est débloqué — définitivement — dès que le total d'Éclats
 * cumulés de l'utilisateur atteint son seuil (threshold_eclats).
 */
class ExerciseUnlocker
{
    public function __construct(protected GamificationEngine $gamification) {}

    /**
     * Met à jour les déblocages pour un utilisateur.
     *
     * @return array<int, DevExercise> Exercices nouvellement débloqués
     */
    public function syncFor(User $user): array
    {
        // Robustesse : si le plugin vient d'être branché mais que les tables
        // n'existent pas encore, on ne fait rien plutôt que de planter.
        if (! Schema::hasTable('dev_exercises') || ! Schema::hasTable('dev_exercise_progress')) {
            return [];
        }

        $total = $this->gamification->totalEclats($user);

        $alreadyUnlocked = DevExerciseProgress::forUser($user->id)
            ->whereNotNull('unlocked_at')
            ->pluck('exercise_slug')
            ->all();

        $toUnlock = DevExercise::query()
            ->active()
            ->where('threshold_eclats', '<=', $total)
            ->when($alreadyUnlocked, fn ($q) => $q->whereNotIn('slug', $alreadyUnlocked))
            ->ordered()
            ->get();

        $newlyUnlocked = [];
        foreach ($toUnlock as $exercise) {
            DevExerciseProgress::updateOrCreate(
                ['user_id' => $user->id, 'exercise_slug' => $exercise->slug],
                ['unlocked_at' => now()]
            );
            $newlyUnlocked[] = $exercise;
        }

        return $newlyUnlocked;
    }

    /**
     * L'utilisateur a-t-il débloqué cet exercice ?
     */
    public function isUnlocked(User $user, DevExercise $exercise): bool
    {
        return $this->gamification->totalEclats($user) >= $exercise->threshold_eclats;
    }
}
