<?php

namespace Praxis\Plugins\PLUGIN_CLASS;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void
    {
        // Lier des services spécifiques au plugin dans le container Laravel.
        // Laisser vide si pas nécessaire.
    }

    public function boot(): void
    {
        // ── 1. Charger les routes du plugin (optionnel) ───────────────────
        // Décommenter si le plugin ajoute des routes propres
        // (ex: page résultats détaillée via son propre controller)
        // $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));

        // ── 2. Charger les vues Blade du plugin (optionnel) ───────────────
        // $this->loadViewsFrom($this->pluginPath('resources/views'), $this->slug());

        // ── 3. Charger les migrations du plugin (optionnel) ───────────────
        // Uniquement si le plugin ajoute ses propres tables.
        // $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        // ── 4. Enregistrer le moteur de scoring ───────────────────────────
        // OBLIGATOIRE si le plugin fournit un scoring engine.
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PLUGIN_CLASSScoringEngine());

        // ── 5. Brancher des hooks (actions / filtres) ─────────────────────

        // Exemple : déclencher un traitement à la fin d'une tentative
        // $this->registerActions([
        //     'attempt.completed' => [Listeners\OnAttemptCompleted::class, 'handle'],
        // ]);

        // Exemple : enrichir les messages envoyés à l'IA
        // $this->registerFilters([
        //     'ai.synthesis.messages' => [Listeners\EnrichAiPrompt::class, 'handle'],
        // ]);
    }

    /**
     * Appelé lors de l'activation via l'admin UI ou Artisan.
     * Toujours idempotent (peut être appelé plusieurs fois sans casse).
     */
    public function onActivate(): void
    {
        // Migrations propres au plugin
        // \Artisan::call('migrate', [
        //     '--path'  => 'plugins/' . $this->slug() . '/database/migrations',
        //     '--force' => true,
        // ]);

        // Seeder questions
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PLUGIN_CLASS\\Database\\Seeders\\QuestionsSeeder',
            '--force' => true,
        ]);

        // Seeder normes de référence
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PLUGIN_CLASS\\Database\\Seeders\\NormsSeeder',
            '--force' => true,
        ]);
    }
}
