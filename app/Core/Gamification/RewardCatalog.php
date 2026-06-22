<?php

namespace Praxis\Core\Gamification;

use App\Models\Plugin as PluginModel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Praxis\Core\Plugins\PluginRegistry;

/**
 * Catalogue des « Cadeaux » de La Salle du Trésor.
 *
 * Chaque plugin activé qui déclare un bloc `reward` dans son manifest devient
 * une récompense déblocable par paliers d'Éclats cumulés (accumulation, jamais
 * de dépense — cohérent avec PraxiBoost / le moteur de gamification).
 *
 * Un reward est débloqué — définitivement — dès que le total d'Éclats de
 * l'utilisateur atteint son `threshold_eclats`.
 */
class RewardCatalog
{
    /** Cache mémoire (par requête) du catalogue normalisé. */
    protected ?Collection $cached = null;

    public function __construct(
        protected GamificationEngine $gamification,
        protected PluginRegistry $registry,
    ) {}

    /**
     * Catalogue brut, trié par seuil croissant.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function all(): Collection
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        // Robustesse : avant migration de la table plugins, ne rien renvoyer.
        if (! Schema::hasTable('plugins')) {
            return $this->cached = collect();
        }

        $enabled = PluginModel::where('enabled', true)->get();

        $items = $enabled->map(function (PluginModel $plugin) {
            // Manifest frais depuis le disque si dispo, sinon celui en base.
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
                'estimated_minutes' => $test['estimated_minutes'] ?? null,
                'entry'             => $this->resolveEntry($test, $reward),
            ];
        })
        ->filter()
        ->sortBy('threshold')
        ->values();

        return $this->cached = $items;
    }

    /**
     * Détermine le point d'entrée d'un reward.
     * - test    : ['type' => 'test',  'slug' => ..., 'url' => route('tests.show', slug)]
     * - route   : ['type' => 'route', 'name' => ..., 'url' => route(name)]
     * - none    : ['type' => 'none',  'url' => null]
     *
     * @return array<string, mixed>
     */
    protected function resolveEntry(?array $test, array $reward): array
    {
        if (! empty($test['slug'])) {
            return [
                'type' => 'test',
                'slug' => $test['slug'],
                'url'  => Route::has('tests.show') ? route('tests.show', $test['slug']) : null,
            ];
        }

        if (! empty($reward['entry_route'])) {
            $name = $reward['entry_route'];

            return [
                'type' => 'route',
                'name' => $name,
                'url'  => Route::has($name) ? route($name) : null,
            ];
        }

        return ['type' => 'none', 'url' => null];
    }

    /**
     * Catalogue contextualisé pour un utilisateur (état de déblocage + progression).
     *
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $total = $this->gamification->totalEclats($user);

        $items = $this->all()->map(function (array $r) use ($total) {
            $unlocked  = $total >= $r['threshold'];
            $remaining = max(0, $r['threshold'] - $total);
            $progress  = $r['threshold'] > 0
                ? (int) min(100, round(($total / $r['threshold']) * 100))
                : 100;

            return array_merge($r, [
                'unlocked'     => $unlocked,
                'remaining'    => $remaining,
                'progress_pct' => $unlocked ? 100 : $progress,
                // Lien réel seulement si débloqué (évite de fuiter l'accès côté front).
                'url'          => $unlocked ? ($r['entry']['url'] ?? null) : null,
            ]);
        })->values()->all();

        $unlockedCount = collect($items)->where('unlocked', true)->count();

        return [
            'total'          => $total,
            'unlocked_count' => $unlockedCount,
            'total_count'    => count($items),
            'items'          => $items,
        ];
    }

    /**
     * Slugs de tests adossés à un reward (pour les exclure de L'Armurerie
     * et appliquer le gating sur tests.show).
     *
     * @return array<int, string>
     */
    public function testSlugs(): array
    {
        return $this->all()
            ->filter(fn ($r) => $r['entry']['type'] === 'test')
            ->pluck('entry.slug')
            ->all();
    }

    /** Reward associé à un slug de test, le cas échéant. */
    public function rewardForTestSlug(string $slug): ?array
    {
        return $this->all()
            ->first(fn ($r) => $r['entry']['type'] === 'test' && $r['entry']['slug'] === $slug);
    }

    /** Reward associé à un nom de route, le cas échéant. */
    public function rewardForRoute(string $routeName): ?array
    {
        return $this->all()
            ->first(fn ($r) => $r['entry']['type'] === 'route' && ($r['entry']['name'] ?? null) === $routeName);
    }

    /** Le test est-il débloqué pour cet utilisateur ? (true si non concerné par un reward) */
    public function isTestUnlocked(string $slug, User $user): bool
    {
        $reward = $this->rewardForTestSlug($slug);

        if ($reward === null) {
            return true;
        }

        return $this->gamification->totalEclats($user) >= $reward['threshold'];
    }

    /** La route (mini-app) est-elle débloquée pour cet utilisateur ? */
    public function isRouteUnlocked(string $routeName, User $user): bool
    {
        $reward = $this->rewardForRoute($routeName);

        if ($reward === null) {
            return true;
        }

        return $this->gamification->totalEclats($user) >= $reward['threshold'];
    }
}
