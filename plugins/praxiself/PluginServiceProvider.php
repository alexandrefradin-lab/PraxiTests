<?php

namespace Praxis\Plugins\PraxiSelf;

use Praxis\Core\Library\ExerciseLibrary;
use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    private const CATEGORIES = [
        'confiance'     => 'Confiance en soi',
        'assertivite'   => 'Affirmation de soi',
        'communication' => 'Communication',
        'roleplay'      => 'Mises en situation',
    ];

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
            ->registerScoringEngine(new Scoring\PraxiSelfScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxiself-scoring' ? 'PraxiSelfResult' : $page,
        ]);

        // Salle du Trésor : bibliothèque d'exercices (plus de test à l'entrée).
        app(ExerciseLibrary::class)->register('praxiself', [
            'title'     => 'La Forge du Soi',
            'subtitle'  => 'Des exercices guidés pour bâtir une affirmation de soi solide, à ton rythme.',
            'icon'      => 'ti-flame',
            'exercises' => fn () => self::libraryExercises(),
            'tips'      => fn () => Data\Tips::all(),
        ]);
    }

    /** Catalogue normalisé pour la bibliothèque d'exercices. */
    private static function libraryExercises(): array
    {
        return array_map(function (array $e) {
            $cat = $e['category'] ?? null;
            $ins = $e['instructions'] ?? null;

            return [
                'id'           => $e['id'],
                'title'        => $e['title'],
                'category'     => self::CATEGORIES[$cat] ?? ucfirst((string) $cat),
                'duration_min' => $e['duration_minutes'] ?? null,
                'summary'      => $e['scientific_basis'] ?? null,
                // Les instructions PraxiSelf sont un texte multi-étapes -> body Markdown.
                'body'         => is_string($ins) ? $ins : null,
                'steps'        => is_array($ins) ? array_values($ins) : [],
            ];
        }, Data\Exercises::all());
    }

    public function onActivate(): void
    {
        // Migration en premier : les tables doivent exister avant le seed.
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxiself/database/migrations',
            '--force' => true,
        ]);

        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiSelf\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }
}
