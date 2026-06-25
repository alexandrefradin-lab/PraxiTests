<?php

namespace Praxis\Plugins\PraxiFocus;

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
        // Enregistrer le moteur de scoring ASRS-v1.1.
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiFocusScoringEngine());

        // Brancher la page de résultats custom (sinon fallback ResultsShow générique).
        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxifocus-asrs' ? 'PraxiFocusResult' : $page,
        ]);
    }

    /**
     * Activation via l'admin UI ou Artisan. Idempotent.
     */
    public function onActivate(): void
    {
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiFocus\\Database\\Seeders\\QuestionsSeeder',
            '--force' => true,
        ]);

        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiFocus\\Database\\Seeders\\NormsSeeder',
            '--force' => true,
        ]);
    }
}
