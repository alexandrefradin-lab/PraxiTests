<?php

namespace Praxis\Plugins\PraxiMum;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\BigFiveScoringEngine());
    }

    public function onActivate(): void
    {
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiMum\\Database\\Seeders\\PraxiMumQuestionsSeeder',
            '--force' => true,
        ]);
    }
}
