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

        $this->app->singleton(PluginRegistry::class);
        $this->app->singleton(PluginManager::class, fn ($app) => new PluginManager($app, $app->make(PluginRegistry::class)));
        $this->app->singleton(AIManager::class);
        $this->app->singleton(TestEngine::class);
        $this->app->singleton(GamificationEngine::class);
        $this->app->singleton(NeuromarketingOptimizer::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\PluginsDiscover::class,
                \App\Console\Commands\PluginActivate::class,
            ]);
        }

        // Boot plugins activés
        $this->app->make(PluginManager::class)->bootEnabledPlugins();
    }
}
