<?php

namespace Praxis\Plugins\PraxiSelf;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadViewsFrom($this->pluginPath('resources/views'), $this->slug());
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiSelfScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxiself-scoring' ? 'PraxiSelfResult' : $page,
        ]);
    }

    public function onActivate(): void
    {
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiSelf\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);

        // Migration du parcours 60 jours
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxiself/database/migrations',
            '--force' => true,
        ]);
    }
}
