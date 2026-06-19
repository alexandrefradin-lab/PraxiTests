<?php

namespace Praxis\Plugins\Praxis360;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void
    {
        // Aucun service spécifique à lier.
    }

    public function boot(): void
    {
        // Enregistrer le moteur de scoring (obligatoire).
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\Praxis360ScoringEngine());
    }

    /**
     * Appelé lors de l'activation via l'admin UI ou Artisan.
     * Toujours idempotent.
     */
    public function onActivate(): void
    {
        // Seeder questions
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\Praxis360\\Database\\Seeders\\QuestionsSeeder',
            '--force' => true,
        ]);

        // Seeder normes de référence
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\Praxis360\\Database\\Seeders\\NormsSeeder',
            '--force' => true,
        ]);
    }
}
