<?php

namespace Praxis\Core\Plugins;

use Illuminate\Support\ServiceProvider;
use Praxis\Core\Plugins\Contracts\PluginContract;

abstract class AbstractPlugin extends ServiceProvider implements PluginContract
{
    protected array $manifestData = [];

    public function setManifest(array $manifest): self
    {
        $this->manifestData = $manifest;
        return $this;
    }

    public function manifest(): array
    {
        return $this->manifestData;
    }

    public function slug(): string
    {
        return $this->manifestData['slug'] ?? class_basename(static::class);
    }

    public function name(): string
    {
        return $this->manifestData['name'] ?? $this->slug();
    }

    public function version(): string
    {
        return $this->manifestData['version'] ?? '0.0.0';
    }

    public function type(): string
    {
        return $this->manifestData['type'] ?? 'integration';
    }

    public function permissions(): array
    {
        return $this->manifestData['permissions'] ?? [];
    }

    public function onInstall(): void {}
    public function onActivate(): void {}
    public function onDeactivate(): void {}
    public function onUninstall(): void {}

    /** Helpers à appeler dans register/boot des plugins */

    protected function registerActions(array $map): void
    {
        foreach ($map as $event => $handler) {
            PluginHooks::action($event, $handler);
        }
    }

    protected function registerFilters(array $map): void
    {
        foreach ($map as $event => $handler) {
            PluginHooks::filter($event, $handler);
        }
    }

    protected function pluginPath(string $path = ''): string
    {
        $base = config('plugins.path') . DIRECTORY_SEPARATOR . $this->slug();
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }

    /**
     * Surcharge : n'enregistre les vues que si le dossier existe vraiment.
     * Les plugins Vue/Inertia n'ont pas de dossier resources/views ; sans ce
     * garde-fou, `php artisan view:cache` plante (Finder lève une exception
     * sur un dossier absent) et casse tout le déploiement.
     */
    protected function loadViewsFrom($path, $namespace)
    {
        $paths = array_values(array_filter((array) $path, 'is_dir'));
        if (! empty($paths)) {
            parent::loadViewsFrom($paths, $namespace);
        }
    }

    /**
     * Surcharge : idem pour les migrations (dossier optionnel selon le plugin).
     */
    protected function loadMigrationsFrom($paths)
    {
        $existing = array_values(array_filter((array) $paths, 'is_dir'));
        if (! empty($existing)) {
            parent::loadMigrationsFrom($existing);
        }
    }
}
