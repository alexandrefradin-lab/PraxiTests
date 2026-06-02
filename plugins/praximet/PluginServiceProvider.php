<?php

namespace Praxis\Plugins\PraxiMet;

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
            ->registerScoringEngine(new Scoring\RiasecScoringEngine());

        $this->registerActions([
            'attempt.completed' => [Listeners\OnAttemptCompleted::class, 'handle'],
        ]);

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praximet-riasec' ? 'PraximetResult' : $page,
        ]);
    }


    public function onActivate(): void
    {
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praximet/database/migrations',
            '--force' => true,
        ]);
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiMet\\Database\\Seeders\\RiasecQuestionsSeeder',
            '--force' => true,
        ]);
    }
}
