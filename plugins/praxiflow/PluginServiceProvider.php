<?php

namespace Praxis\Plugins\PraxiFlow;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiFlowScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxiflow-scoring' ? 'PraxiFlowResult' : $page,
        ]);
    }

    public function onActivate(): void
    {
        // Migrations
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxiflow/database/migrations',
            '--force' => true,
        ]);

        // Seeders
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiFlow\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }
}
