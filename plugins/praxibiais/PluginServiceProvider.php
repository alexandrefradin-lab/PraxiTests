<?php

namespace Praxis\Plugins\PraxiBiais;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\BiaisScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxibiais-cognitif' ? 'PraxiBiaisResult' : $page,
        ]);
    }

    public function onActivate(): void
    {
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiBiais\\Database\\Seeders\\PraxiBiaisQuestionsSeeder',
            '--force' => true,
        ]);
    }
}
