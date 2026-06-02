<?php

namespace Praxis\Plugins\PraxiValeurs;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\SchwartzScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxivaleurs-schwartz' ? 'PraxiValeursResult' : $page,
        ]);
    }

    public function onActivate(): void
    {
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiValeurs\\Database\\Seeders\\ValuesQuestionsSeeder',
            '--force' => true,
        ]);
    }
}
