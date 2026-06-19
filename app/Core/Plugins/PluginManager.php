<?php

namespace Praxis\Core\Plugins;

use App\Models\Plugin as PluginModel;
use Illuminate\Contracts\Foundation\Application;
use Praxis\Core\Plugins\Contracts\PluginContract;

/**
 * Charge et active dynamiquement les plugins activés en DB.
 * Appelé depuis PraxiQuestServiceProvider::register().
 */
class PluginManager
{
    /** @var array<string, AbstractPlugin> */
    protected array $loaded = [];

    public function __construct(
        protected Application $app,
        protected PluginRegistry $registry,
    ) {}

    public function bootEnabledPlugins(): void
    {
        if (!$this->databaseReady()) {
            return;
        }

        $enabled = PluginModel::query()->where('enabled', true)->get();

        foreach ($enabled as $row) {
            try {
                $this->loadPlugin($row);
            } catch (\Throwable $e) {
                logger()->error("PraxiQuest plugin boot failed [{$row->slug}]: {$e->getMessage()}");
                $this->logToDb($row->id, 'error', 'boot.failed', $e->getMessage());
            }
        }
    }

    /**
     * SEC-05 : valide que le service_provider est un FQCN autorisé
     * (Praxis\Plugins\*) pour éviter toute RCE si la DB est compromise.
     */
    protected function validateProviderClass(string $class): void
    {
        // Doit être un FQCN valide (lettres, chiffres, antislashs)
        if (!preg_match('/^[A-Za-z0-9\\\\]+$/', $class)) {
            throw new \RuntimeException("Invalid service_provider class name: {$class}");
        }

        // Doit appartenir au namespace autorisé
        $allowed = config('plugins.allowed_namespaces', ['Praxis\\Plugins\\']);
        foreach ($allowed as $ns) {
            if (str_starts_with($class, $ns)) {
                return;
            }
        }

        throw new \RuntimeException(
            "Service provider '{$class}' is outside the allowed namespaces. " .
            "Update config/plugins.php to add it explicitly."
        );
    }

    protected function loadPlugin(PluginModel $row): void
    {
        $manifest = $row->manifest ?? $this->registry->findManifest($row->slug);
        if (!$manifest) {
            return;
        }

        $providerClass = $manifest['service_provider'];

        // SEC-05 : valider le namespace avant tout chargement
        $this->validateProviderClass($providerClass);

        if (!class_exists($providerClass)) {
            $this->autoloadPlugin($manifest);
            if (!class_exists($providerClass)) {
                throw new \RuntimeException("Service provider not found: {$providerClass}");
            }
        }

        /** @var AbstractPlugin $provider */
        $provider = new $providerClass($this->app);
        if (!$provider instanceof PluginContract) {
            throw new \RuntimeException("Provider {$providerClass} must extend AbstractPlugin");
        }

        $provider->setManifest($manifest);
        $this->app->register($provider);
        $this->loaded[$row->slug] = $provider;

        PluginHooks::doAction('plugin.booted', $row->slug, $provider);
    }

    protected function autoloadPlugin(array $manifest): void
    {
        // Si le plugin a son propre composer / autoload, on l'inclut.
        $autoload = ($manifest['_path'] ?? '') . '/vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
            return;
        }

        // Fallback : charge tout le src/ en PSR-4 simplifié.
        $src = ($manifest['_path'] ?? '') . '/src';
        if (is_dir($src)) {
            $namespace = trim($manifest['namespace'] ?? '', '\\');
            spl_autoload_register(function ($class) use ($namespace, $src) {
                if ($namespace && !str_starts_with($class, $namespace . '\\')) {
                    return;
                }
                $relative = $namespace ? substr($class, strlen($namespace) + 1) : $class;
                $file = $src . '/' . str_replace('\\', '/', $relative) . '.php';
                if (is_file($file)) {
                    require_once $file;
                }
            });
        }
    }

    public function activate(string $slug): void
    {
        $plugin = PluginModel::where('slug', $slug)->firstOrFail();
        $plugin->update(['enabled' => true, 'last_activated_at' => now()]);

        if (!isset($this->loaded[$slug])) {
            $this->loadPlugin($plugin);
        }
        $this->loaded[$slug]?->onActivate();
        $this->logToDb($plugin->id, 'info', 'activated', 'Plugin activated');
        PluginHooks::doAction('plugin.activated', $slug);
    }

    public function deactivate(string $slug): void
    {
        $plugin = PluginModel::where('slug', $slug)->firstOrFail();
        if ($plugin->core) {
            throw new \RuntimeException("Cannot deactivate core plugin '{$slug}'");
        }
        $this->loaded[$slug]?->onDeactivate();
        $plugin->update(['enabled' => false]);
        $this->logToDb($plugin->id, 'info', 'deactivated', 'Plugin deactivated');
        PluginHooks::doAction('plugin.deactivated', $slug);
    }

    public function uninstall(string $slug): void
    {
        $plugin = PluginModel::where('slug', $slug)->firstOrFail();
        if ($plugin->core) {
            throw new \RuntimeException("Cannot uninstall core plugin '{$slug}'");
        }
        $this->loaded[$slug]?->onUninstall();
        $plugin->delete();
        PluginHooks::doAction('plugin.uninstalled', $slug);
    }

    public function isLoaded(string $slug): bool
    {
        return isset($this->loaded[$slug]);
    }

    public function get(string $slug): ?AbstractPlugin
    {
        return $this->loaded[$slug] ?? null;
    }

    public function loaded(): array
    {
        return $this->loaded;
    }

    protected function databaseReady(): bool
    {
        try {
            return \Schema::hasTable('plugins');
        } catch (\Throwable) {
            return false;
        }
    }

    protected function logToDb(int $pluginId, string $level, string $event, string $message): void
    {
        try {
            \DB::table('plugin_logs')->insert([
                'plugin_id' => $pluginId,
                'level'     => $level,
                'event'     => $event,
                'message'   => $message,
                'context'   => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // best-effort
        }
    }
}
