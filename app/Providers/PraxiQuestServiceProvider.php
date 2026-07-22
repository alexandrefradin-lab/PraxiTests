<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Praxis\Core\AI\AIManager;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Mailing\NeuromarketingOptimizer;
use Praxis\Core\Plugins\PluginManager;
use Praxis\Core\Plugins\PluginRegistry;
use Praxis\Core\TestEngine\TestEngine;

class PraxiQuestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/praxiquest.php', 'praxiquest');
        $this->mergeConfigFrom(__DIR__ . '/../../config/plugins.php', 'plugins');
        $this->mergeConfigFrom(__DIR__ . '/../../config/ai.php', 'ai');
        $this->mergeConfigFrom(__DIR__ . '/../../config/gamification.php', 'gamification');
        $this->mergeConfigFrom(__DIR__ . '/../../config/neuromarketing.php', 'neuromarketing');
        $this->mergeConfigFrom(__DIR__ . '/../../config/protection.php', 'protection');

        $this->app->singleton(PluginRegistry::class);
        $this->app->singleton(PluginManager::class, fn ($app) => new PluginManager($app, $app->make(PluginRegistry::class)));
        $this->app->singleton(AIManager::class);
        $this->app->singleton(TestEngine::class);
        $this->app->singleton(GamificationEngine::class);
        $this->app->singleton(NeuromarketingOptimizer::class);

        // Dispositif anti-copie (cf. config/protection.php). Singletons : ces
        // services mémorisent leur verdict pour la durée de la requête.
        $this->app->singleton(\Praxis\Core\Protection\LicenseService::class);
        $this->app->singleton(\Praxis\Core\Protection\ScrapingGuard::class);
        $this->app->singleton(\Praxis\Core\Protection\DeviceGuard::class);
        $this->app->singleton(\Praxis\Core\Protection\DocumentWatermark::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\PluginsDiscover::class,
                \App\Console\Commands\PluginActivate::class,
                // Dispositif anti-copie
                \App\Console\Commands\LicenseKeygen::class,
                \App\Console\Commands\LicenseIssue::class,
                \App\Console\Commands\LicenseStatus::class,
                \App\Console\Commands\PdfTrace::class,
                \App\Console\Commands\ProtectionReport::class,
            ]);
        }

        // Boot plugins activés
        $this->app->make(PluginManager::class)->bootEnabledPlugins();
    }
}
