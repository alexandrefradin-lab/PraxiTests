<?php

namespace Praxis\Core\Gamification;

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

    public function forUser(User $user): array
    {
        $total = $this->gamification->totalEclats($user);

        $items = $this->all()->map(function (array $r) use ($total, $user) {
            $unlocked  = $total >= $r['threshold'];
            $remaining = max(0, $r['threshold'] - $total);
            $progress  = $r['threshold'] > 0
                ? (int) min(100, round(($total / $r['threshold']) * 100))
                : 100;

            $match = $r['_profile_match']
                ? $this->profileMatch->evaluate($user, $r['_profile_match'])
                : ['recommended' => false, 'match_score' => 0, 'match_reason' => null];

            $clean = array_diff_key($r, ['_profile_match' => true]);

            return array_merge($clean, [
                'unlocked'      => $unlocked,
                'remaining'     => $remaining,
                'progress_pct'  => $unlocked ? 100 : $progress,
                'url'           => $unlocked ? ($r['entry']['url'] ?? null) : null,
                'recommended'   => $match['recommended'],
                'match_score'   => $match['match_score'],
                'match_reason'  => $match['match_reason'],
            ]);
        });

        $sorted = $items->sortBy(function (array $item) {
            if ($item['unlocked'] && $item['recommended'])  return 0;
            if ($item['unlocked'] && !$item['recommended']) return 1;
            if (!$item['unlocked'] && $item['recommended']) return 2;
            return 3;
        })->values()->all();

        $unlockedCount = collect($sorted)->where('unlocked', true)->count();

        return [
            'total'           => $total,
            'unlocked_count'  => $unlockedCount,
            'total_count'     => count($sorted),
            'has_profile'     => $user->profileGrimoire?->status === 'ready',
            'items'           => $sorted,
        ];
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

    public function isTestUnlocked(string $slug, User $user): bool
    {
        $reward = $this->rewardForTestSlug($slug);

        if ($reward === null) {
            return true;
        }

        return $this->gamification->totalEclats($user) >= $reward['threshold'];
    }

    public function isRouteUnlocked(string $routeName, User $user): bool
    {
        $reward = $this->rewardForRoute($routeName);

        if ($reward === null) {
            return true;
        }

        return $this->gamification->totalEclats($user) >= $reward['threshold'];
    }
}
