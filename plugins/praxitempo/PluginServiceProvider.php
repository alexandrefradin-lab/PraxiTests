<?php

namespace Praxis\Plugins\PraxiTempo;

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
        // Moteur de scoring (obligatoire).
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiTempoScoringEngine());

        $this->registerFilters([
            // Passation conversationnelle : on remplace le runner générique
            // (Candidate/AttemptPlay) par la page chat dédiée à ce test.
            'attempt.inertia_page' => fn (string $page, $attempt) =>
                ($attempt->test->scoring_engine ?? null) === 'praxitempo-scoring'
                    ? 'PraxiTempoPlay'
                    : $page,

            // Page de résultats : laissée au générique Candidate/ResultsShow pour la v1.
            // (Le scoring expose dimension_meta + archétype, donc l'écran générique
            //  affiche déjà des libellés parlants.)
        ]);
    }

    /**
     * Activation idempotente : crée le test + ses questions, puis les normes.
     */
    public function onActivate(): void
    {
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiTempo\\Database\\Seeders\\QuestionsSeeder',
            '--force' => true,
        ]);

        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiTempo\\Database\\Seeders\\NormsSeeder',
            '--force' => true,
        ]);
    }

    public function slug(): string
    {
        return 'praxitempo';
    }
}
