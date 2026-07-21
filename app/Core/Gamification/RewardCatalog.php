<?php

namespace Praxis\Core\Gamification;

use App\Models\MiniAppUnlock;
use App\Models\Plugin as PluginModel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Praxis\Core\Journey\JourneyRegistry;
use Praxis\Core\Plugins\PluginRegistry;

/**
 * Catalogue des cadeaux de La Salle du Tresor.
 */
class RewardCatalog
{
    protected ?Collection $cached = null;

    public function __construct(
        protected GamificationEngine $gamification,
        protected PluginRegistry $registry,
        protected ProfileMatchService $profileMatch,
    ) {}

    public function all(): Collection
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        if (! Schema::hasTable('plugins')) {
            return $this->cached = collect();
        }

        // v2 : URLs relatives (cf. resolveEntry) — bump de clé pour invalider
        // les anciens caches contenant des URLs absolues.
        $this->cached = Cache::remember('reward_catalog_v2', 300, function () {
            $enabled = PluginModel::where('enabled', true)->get();

            return $enabled->map(function (PluginModel $plugin) {
                $manifest = $this->registry->findManifest($plugin->slug) ?? $plugin->manifest ?? [];
                $reward   = $manifest['reward'] ?? null;

                if (! is_array($reward) || ! isset($reward['threshold_eclats'])) {
                    return null;
                }

                $test = $manifest['test'] ?? null;

                return [
                    'plugin_slug'       => $plugin->slug,
                    'name'              => $test['name'] ?? $manifest['name'] ?? $plugin->slug,
                    'purpose'           => $reward['purpose'] ?? null,
                    'description'       => $test['description'] ?? $manifest['description'] ?? '',
                    'teaser'            => $reward['teaser'] ?? ($manifest['description'] ?? ''),
                    'icon'              => $reward['icon'] ?? 'ti-gift',
                    'threshold'         => (int) $reward['threshold_eclats'],
                    'estimated_minutes' => JourneyRegistry::has($plugin->slug) ? null : ($test['estimated_minutes'] ?? null),
                    'entry'             => $this->resolveEntry($plugin->slug, $test, $reward),
                    '_profile_match'    => $manifest['profile_match'] ?? null,
                ];
            })
            ->filter()
            ->sortBy('threshold')
            ->values();
        });

        return $this->cached;
    }

    protected function resolveEntry(string $slug, ?array $test, array $reward): array
    {
        // IMPORTANT : URLs RELATIVES (absolute: false). Le catalogue est mis en
        // cache 5 min pour TOUS les utilisateurs : une URL absolue figerait
        // l'hôte de la requête qui a rempli le cache (praxiquest.fr vs
        // www.praxiquest.fr) et casserait la navigation Inertia (cross-origin
        // silencieux) pour les visiteurs de l'autre domaine.

        // Un plugin enregistré comme parcours 60 jours pointe vers son tableau
        // de bord (prioritaire sur l'ancien questionnaire/test).
        if (JourneyRegistry::has($slug)) {
            return [
                'type' => 'journey',
                'slug' => $slug,
                'url'  => Route::has('journey.index') ? route('journey.index', ['plugin' => $slug], false) : null,
            ];
        }

        if (! empty($test['slug'])) {
            return [
                'type' => 'test',
                'slug' => $test['slug'],
                'url'  => Route::has('tests.show') ? route('tests.show', $test['slug'], false) : null,
            ];
        }

        if (! empty($reward['entry_route'])) {
            $name = $reward['entry_route'];

            return [
                'type' => 'route',
                'name' => $name,
                'url'  => Route::has($name) ? route($name, [], false) : null,
            ];
        }

        return ['type' => 'none', 'url' => null];
    }

    /**
     * Déblocage CHOISI actif ? Livré à false : la bascule se fait via
     * PRAXIQUEST_TREASURE_CHOICE_ENABLED (cf. config/praxiquest.php).
     */
    public function choiceEnabled(): bool
    {
        return (bool) config('praxiquest.treasure.choice_enabled', false);
    }

    public function forUser(User $user): array
    {
        $choice = $this->choiceEnabled();
        $total  = $this->gamification->totalEclats($user);

        // Régime historique : pas de dépense, pas de porte, le cumul fait foi.
        $spent     = $choice ? MiniAppUnlock::spentBy($user->id) : 0;
        $available = max(0, $total - $spent);

        $owned = array_flip($this->unlockedSlugs($user));

        $items = $this->all()->map(function (array $r) use ($choice, $available, $owned, $user) {
            $cost     = (int) $r['threshold'];
            $unlocked = $choice
                ? isset($owned[$r['plugin_slug']])
                : $available >= $cost;
            $missing  = max(0, $cost - $available);

            // Progression = avancement du PORTEFEUILLE vers le coût, pas du cumul :
            // ce qui a déjà été dépensé ne doit plus compter comme acquis.
            $progress = $cost > 0
                ? (int) min(100, round(($available / $cost) * 100))
                : 100;

            $match = $r['_profile_match']
                ? $this->profileMatch->evaluate($user, $r['_profile_match'])
                : ['recommended' => false, 'match_score' => 0, 'match_reason' => null];

            $clean = array_diff_key($r, ['_profile_match' => true]);

            return array_merge($clean, [
                'unlocked'      => $unlocked,
                'cost'          => $cost,
                // En régime historique rien ne s'achète : pas de bouton d'ouverture.
                'affordable'    => $choice && ! $unlocked && $missing === 0,
                'missing'       => $unlocked ? 0 : $missing,
                'remaining'     => $unlocked ? 0 : $missing,
                'progress_pct'  => $unlocked ? 100 : $progress,
                'url'           => $unlocked ? ($r['entry']['url'] ?? null) : null,
                'recommended'   => $match['recommended'],
                'match_score'   => $match['match_score'],
                'match_reason'  => $match['match_reason'],
            ]);
        });

        // Ordre de lecture : ce que je possède, puis ce que je peux m'offrir
        // maintenant, puis le reste — la recommandation profil départage.
        $sorted = $items->sortBy(function (array $item) {
            if ($item['unlocked'] && $item['recommended'])   return 0;
            if ($item['unlocked'])                           return 1;
            if ($item['affordable'] && $item['recommended']) return 2;
            if ($item['affordable'])                         return 3;
            if ($item['recommended'])                        return 4;
            return 5;
        })->values()->all();

        $unlockedCount = collect($sorted)->where('unlocked', true)->count();

        return [
            'total'           => $total,
            'spent'           => $spent,
            'available'       => $available,
            'choice_enabled'  => $choice,
            'unlocked_count'  => $unlockedCount,
            'total_count'     => count($sorted),
            'has_profile'     => $user->profileGrimoire?->status === 'ready',
            'items'           => $sorted,
        ];
    }

    /** @return string[] slugs des mini-apps que ce candidat a ouvertes */
    public function unlockedSlugs(User $user): array
    {
        return MiniAppUnlock::slugsFor($user->id);
    }

    /**
     * Description longue d'un plugin (manifest), affichée en tête de sa
     * mini-app — la même que sur sa carte de la Salle du Trésor.
     */
    public function descriptionFor(string $pluginSlug): ?string
    {
        $desc = $this->all()->firstWhere('plugin_slug', $pluginSlug)['description'] ?? '';

        return $desc !== '' ? $desc : null;
    }

    public function testSlugs(): array
    {
        return $this->all()
            ->filter(fn ($r) => $r['entry']['type'] === 'test')
            ->pluck('entry.slug')
            ->all();
    }

    public function rewardForTestSlug(string $slug): ?array
    {
        return $this->all()
            ->first(fn ($r) => $r['entry']['type'] === 'test' && $r['entry']['slug'] === $slug);
    }

    public function rewardForRoute(string $routeName): ?array
    {
        return $this->all()
            ->first(fn ($r) => $r['entry']['type'] === 'route' && ($r['entry']['name'] ?? null) === $routeName);
    }

    /**
     * Récompense adossée à un parcours 60 jours (entry.type === 'journey').
     * Avant, ces plugins échappaient à rewardForRoute (qui ne matche que 'route')
     * → isRouteUnlocked retournait toujours true → gating inopérant.
     */
    public function rewardForJourney(string $slug): ?array
    {
        return $this->all()
            ->first(fn ($r) => $r['entry']['type'] === 'journey' && ($r['entry']['slug'] ?? null) === $slug);
    }

    /**
     * Toutes les gardes reposent désormais sur un déblocage PERSISTÉ et CHOISI
     * (table mini_app_unlocks) et non plus sur une comparaison de seuil : depuis
     * que les Éclats se dépensent, le cumul ne dit plus ce que le candidat possède.
     */
    protected function ownsReward(?array $reward, User $user): bool
    {
        if ($reward === null) {
            return true; // pas un cadeau : accès libre
        }

        // Régime historique (flag off) : le cumul d'Éclats fait foi, comme avant.
        if (! $this->choiceEnabled()) {
            return $this->gamification->totalEclats($user) >= $reward['threshold'];
        }

        return in_array($reward['plugin_slug'], $this->unlockedSlugs($user), true);
    }

    public function isTestUnlocked(string $slug, User $user): bool
    {
        return $this->ownsReward($this->rewardForTestSlug($slug), $user);
    }

    public function isRouteUnlocked(string $routeName, User $user): bool
    {
        return $this->ownsReward($this->rewardForRoute($routeName), $user);
    }

    /** Déblocage d'une mini-app enregistrée comme parcours 60 jours. */
    public function isJourneyUnlocked(string $slug, User $user): bool
    {
        return $this->ownsReward($this->rewardForJourney($slug), $user);
    }

    /**
     * Garde de déblocage pour une mini-app à route dédiée (praxilead, praximiroir,
     * praxizenith…). Retourne une redirection vers le Trésor si verrouillée, null
     * sinon. À appeler en tête de index/show/complete pour fermer le contournement
     * par URL directe (SEC-M1).
     */
    public function unlockRedirect(string $routeName, User $user): ?\Illuminate\Http\RedirectResponse
    {
        if ($this->isRouteUnlocked($routeName, $user)) {
            return null;
        }

        return $this->sealedRedirect($this->rewardForRoute($routeName)['threshold'] ?? null, $user);
    }

    /** Garde de déblocage pour une mini-app « parcours 60 jours » (JourneyRegistry). */
    public function journeyUnlockRedirect(string $slug, User $user): ?\Illuminate\Http\RedirectResponse
    {
        if ($this->isJourneyUnlocked($slug, $user)) {
            return null;
        }

        return $this->sealedRedirect($this->rewardForJourney($slug)['threshold'] ?? null, $user);
    }

    /**
     * Garde de déblocage UNIVERSELLE par slug de plugin, quel que soit le type
     * d'entrée (route, journey, test). C'est la garde à privilégier quand on ne
     * connaît que le slug du plugin : elle couvre les mini-apps de type journey
     * que isRouteUnlocked laissait passer (SEC-M1).
     */
    public function rewardForPlugin(string $slug): ?array
    {
        return $this->all()->firstWhere('plugin_slug', $slug);
    }

    public function isPluginUnlocked(string $slug, User $user): bool
    {
        return $this->ownsReward($this->rewardForPlugin($slug), $user);
    }

    public function pluginUnlockRedirect(string $slug, User $user): ?\Illuminate\Http\RedirectResponse
    {
        if ($this->isPluginUnlocked($slug, $user)) {
            return null;
        }

        return $this->sealedRedirect($this->rewardForPlugin($slug)['threshold'] ?? null, $user);
    }

    /**
     * Redirection commune « trésor scellé » : la mini-app n'a pas encore été
     * ouverte (régime « choix ») ou son palier n'est pas atteint (historique).
     */
    private function sealedRedirect(?int $threshold, User $user): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('treasure.index')->with(
            'error',
            $threshold
                ? \App\Support\Parcours::sealedMessage($threshold, $user)
                : (\App\Support\Parcours::isCorporate($user) ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé.")
        );
    }
}
