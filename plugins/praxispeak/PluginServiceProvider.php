<?php

namespace Praxis\Plugins\PraxiSpeak;

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
            ->registerScoringEngine(new Scoring\PraxiSpeakScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxispeak-scoring' ? 'PraxiSpeakResult' : $page,
        ]);
    }

    public function onActivate(): void
    {
        // Migration du parcours 60 jours
        \Artisan::call('migrate', [
            '--path'  => $this->pluginPath('database/migrations'),
            '--force' => true,
        ]);

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiSpeak\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }
}
