<?php

namespace Praxis\Core\Gamification;

use App\Models\MiniAppUnlock;
use App\Models\User;
use App\Support\Parcours;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Portefeuille d'Éclats et déblocage CHOISI des mini-apps.
 *
 * Modèle économique :
 *   - cumul   = somme des Éclats gagnés (gamification_progress.xp_total) — inchangé,
 *               c'est lui qui pilote le niveau et la barre du layout ;
 *   - dépensé = somme des cost_eclats de mini_app_unlocks ;
 *   - solde   = cumul − dépensé, c'est ce qui permet d'ouvrir une mini-app.
 *
 * On ne décrémente JAMAIS xp_total : la colonne est unsignedInteger (un
 * décrément sous zéro casserait en SQL) et le niveau du candidat ne doit pas
 * régresser parce qu'il s'est offert un trésor.
 */
class MiniAppUnlockService
{
    public function __construct(
        protected GamificationEngine $gamification,
        protected RewardCatalog $catalog,
        protected TestCompletionService $tests,
    ) {}

    /** Éclats déjà dépensés en déblocages. */
    public function spentEclats(User $user): int
    {
        return MiniAppUnlock::spentBy($user->id);
    }

    /** Éclats disponibles pour ouvrir une mini-app. */
    public function availableEclats(User $user): int
    {
        return max(0, $this->gamification->totalEclats($user) - $this->spentEclats($user));
    }

    public function hasUnlocked(User $user, string $slug): bool
    {
        return MiniAppUnlock::where('user_id', $user->id)
            ->where('plugin_slug', $slug)
            ->exists();
    }

    /**
     * Ouvre la mini-app choisie en débitant son coût.
     *
     * Idempotent : une mini-app déjà ouverte n'est ni re-facturée ni refusée
     * (double-clic, requêtes parallèles) — même leçon que la violation d'unicité
     * sur user_badges qui a produit des 500 en production.
     *
     * @return array{reward:array,cost:int,balance:int,already:bool}
     * @throws MiniAppUnlockException
     */
    public function unlock(User $user, string $slug): array
    {
        $reward = $this->catalog->rewardForPlugin($slug);

        if ($reward === null) {
            throw new MiniAppUnlockException(
                Parcours::isCorporate($user)
                    ? "Ce module n'existe pas."
                    : "Ce trésor n'existe pas."
            );
        }

        if ($this->hasUnlocked($user, $slug)) {
            return [
                'reward'  => $reward,
                'cost'    => 0,
                'balance' => $this->availableEclats($user),
                'already' => true,
            ];
        }

        // Porte d'entrée : toutes les Épreuves doivent être passées.
        $progress = $this->tests->summary($user);
        if (! $progress['all_done']) {
            throw new MiniAppUnlockException(Parcours::armorySealedMessage($progress['remaining'], $user));
        }

        $cost = (int) $reward['threshold'];

        return DB::transaction(function () use ($user, $slug, $reward, $cost) {
            // Sérialise les déblocages concurrents du MÊME candidat : sans ce
            // verrou, deux requêtes simultanées liraient le même solde et
            // ouvriraient deux mini-apps avec les Éclats d'une seule.
            User::whereKey($user->id)->lockForUpdate()->first();

            $balance = $this->availableEclats($user);

            if ($balance < $cost) {
                throw new MiniAppUnlockException(
                    Parcours::notEnoughEclatsMessage($cost - $balance, $user)
                );
            }

            try {
                MiniAppUnlock::create([
                    'user_id'     => $user->id,
                    'plugin_slug' => $slug,
                    'cost_eclats' => $cost,
                    'unlocked_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Déblocage concurrent arrivé en premier : rien à facturer.
                // Filet de dernier recours, non couvert par les tests (il faudrait
                // deux requêtes réellement simultanées) : le verrou ci-dessus doit
                // déjà l'empêcher. C'est la violation d'unicité sur user_badges,
                // qui a produit des 500 en production, qui justifie de le garder.
                return [
                    'reward'  => $reward,
                    'cost'    => 0,
                    'balance' => $this->availableEclats($user),
                    'already' => true,
                ];
            }

            $this->forgetCaches($user);

            return [
                'reward'  => $reward,
                'cost'    => $cost,
                'balance' => max(0, $balance - $cost),
                'already' => false,
            ];
        });
    }

    /**
     * Le solde et le compteur de trésors vivent dans le cache Inertia
     * "eclats.{id}" (60 s) — sans purge, le candidat paierait sans voir
     * son solde bouger pendant une minute.
     */
    protected function forgetCaches(User $user): void
    {
        Cache::forget("eclats.{$user->id}");
    }
}
