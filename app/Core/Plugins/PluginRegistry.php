<?php

namespace Praxis\Core\Plugins;

use App\Models\Plugin as PluginModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Registre central : découvre les manifests sur disque
 * et les synchronise avec la table `plugins`.
 */
class PluginRegistry
{
    protected ?Collection $cached = null;

    public function discover(bool $force = false): Collection
    {
        if ($this->cached && !$force) {
            return $this->cached;
        }

        if (!$force && config('plugins.autodiscover')) {
            $cached = Cache::get(config('plugins.cache_key'));
            if ($cached) {
                return $this->cached = collect($cached);
            }
        }

        $path = config('plugins.path');
        if (!File::isDirectory($path)) {
            return $this->cached = collect();
        }

        $manifests = collect();
        foreach (File::directories($path) as $dir) {
            $manifestFile = $dir . DIRECTORY_SEPARATOR . config('plugins.manifest_file');
            if (!File::exists($manifestFile)) {
                continue;
            }
            try {
                $manifest = json_decode(File::get($manifestFile), true, 512, JSON_THROW_ON_ERROR);
                PluginManifestValidator::validate($manifest, $manifestFile);
                $manifest['_path'] = $dir;
                $manifests->push($manifest);
            } catch (\Throwable $e) {
                logger()->warning("PraxiQuest plugin invalid: {$manifestFile} — {$e->getMessage()}");
            }
        }

        Cache::put(config('plugins.cache_key'), $manifests->all(), now()->addHour());
        return $this->cached = $manifests;
    }

    public function syncToDatabase(): array
    {
        $manifests = $this->discover(true);
        $synced = ['installed' => [], 'updated' => [], 'removed' => []];

        $existing = PluginModel::all()->keyBy('slug');
        $foundSlugs = [];

        foreach ($manifests as $manifest) {
            $slug = $manifest['slug'];
            $foundSlugs[] = $slug;

            $data = [
                'name'             => $manifest['name'],
                'version'          => $manifest['version'],
                'author'           => $manifest['author'] ?? null,
                'type'             => $manifest['type'],
                'description'      => $manifest['description'] ?? null,
                'service_provider' => $manifest['service_provider'],
                'manifest'         => $manifest,
                'permissions'      => $manifest['permissions'] ?? [],
            ];

            if ($existing->has($slug)) {
                $plugin = $existing->get($slug);
                if ($plugin->version !== $manifest['version']) {
                    $plugin->update($data);
                    $synced['updated'][] = $slug;
                } else {
                    $plugin->update(['manifest' => $manifest]);
                }
            } else {
                $data['slug'] = $slug;
                $data['installed_at'] = now();
                PluginModel::create($data);
                $synced['installed'][] = $slug;
            }
        }

        // plugins disparus du disque mais encore en DB
        foreach ($existing as $slug => $plugin) {
            if (!in_array($slug, $foundSlugs, true) && !$plugin->core) {
                $plugin->update(['enabled' => false]);
                $synced['removed'][] = $slug;
            }
        }

        Cache::forget(config('plugins.cache_key'));
        return $synced;
    }

    public function findManifest(string $slug): ?array
    {
        return $this->discover()->firstWhere('slug', $slug);
    }
}
