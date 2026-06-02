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

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praximum-bigfive' ? 'PraxiMumResult' : $page,
        ]);
    }

    public functi