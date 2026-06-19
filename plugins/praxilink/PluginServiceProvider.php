<?php

namespace Praxis\Plugins\PraxiLink;

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
            ->registerScoringEngine(new Scoring\PraxiLinkScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxilink-scoring'
                    ? 'PraxiLinkResult'
                    : $page,
        ]);
    }

    public function onActivate(): void
    {
        // Création de la table journey_progress
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxilink/database/migrations',
            '--force' => true,
        ]);

        // Seed des exercices
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiLink\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }

    public function slug(): string
    {
        return 'praxilink';
    }
}
