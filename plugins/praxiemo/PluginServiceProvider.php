<?php

namespace Praxis\Plugins\PraxiEmo;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\EqiScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxiemo-eqi' ? 'PraxiEmoResult' : $page,
        ]);
    }

    public function onActivate(): void
    {
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiEmo\\Database\\Seeders\\PraxiEmoQuestionsSeeder',
            '--force' => true,
        ]);
    }
}
