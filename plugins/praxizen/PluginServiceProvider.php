<?php

namespace Praxis\Plugins\PraxiZen;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadViewsFrom($this->pluginPath('resources/views'), $this->slug());
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiZenScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxizen-stress'
                    ? 'PraxiZenResult'
                    : $page,
        ]);
    }

    public function onActivate(): void
    {
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxizen/database/migrations',
            '--force' => true,
        ]);

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiZen\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }
}
