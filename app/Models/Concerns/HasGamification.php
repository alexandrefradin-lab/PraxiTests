<?php

namespace App\Models\Concerns;

use App\Models\Badge;
use App\Models\GamificationProgress;
use App\Models\UserEasterEgg;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Gamification : XP, badges, easter eggs et progression par test.
 *
 * Extrait de User (refactor god-model, phase 1). La logique d'attribution
 * (award, évaluation des critères) vit dans GamificationEngine/BadgeEvaluator ;
 * ici uniquement le câblage Eloquent et les lectures agrégées.
 */
trait HasGamification
{
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')->withPivot(['earned_at', 'context']);
    }

    public function easterEggs(): HasMany
    {
        return $this->hasMany(UserEasterEgg::class);
    }

    /** Ce secret a-t-il déjà été découvert par cet utilisateur ? */
    public function hasClaimedEasterEgg(string $slug): bool
    {
        return $this->easterEggs()->where('slug', $slug)->exists();
    }

    public function gamificationProgress(): HasMany
    {
        return $this->hasMany(GamificationProgress::class);
    }

    public function totalXp(): int
    {
        return (int) $this->gamificationProgress()->sum('xp_total');
    }
}
