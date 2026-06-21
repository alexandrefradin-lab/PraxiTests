<?php

namespace Praxis\Plugins\PraxiSens;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        // Enregistre le moteur de scoring SPS.
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiSensScoringEngine());

        // Surcharge la page de résultats pour ce test.
        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxisens-sps' ? 'PraxiSensResult' : $page,
        ]);
    }

    /**
     * Activation idempotente : (re)seed des questions puis des normes.
     */
    public function onActivate(): void
    {
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiSens\\Database\\Seeders\\PraxiSensQuestionsSeeder',
            '--force' => true,
        ]);

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiSens\\Database\\Seeders\\NormsSeeder',
            '--force' => true,
        ]);
    }
}
