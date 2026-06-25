<?php

namespace Praxis\Plugins\PraxiLink;

use Praxis\Core\Library\ExerciseLibrary;
use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    private const CATEGORIES = [
        'ecoute'      => 'Écoute active',
        'cnv'         => 'Communication non-violente',
        'conflit'     => 'Gestion des conflits',
        'feedback'    => 'Feedback constructif',
        'assertivite' => 'Assertivité',
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
            ->registerScoringEngine(new Scoring\PraxiLinkScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxilink-scoring'
                    ? 'PraxiLinkResult'
                    : $page,
        ]);

        // Salle du Trésor : bibliothèque d'exercices (plus de test à l'entrée).
        app(ExerciseLibrary::class)->register('praxilink', [
            'title'     => 'L\'Art des Liens',
            'subtitle'  => 'Des mises en situation guidées : écoute active, CNV, gestion des conflits.',
            'icon'      => 'ti-messages',
            'exercises' => fn () => self::libraryExercises(),
            'tips'      => fn () => Data\Tips::all(),
        ]);
    }

    /** Catalogue normalisé pour la bibliothèque d'exercices. */
    private static function libraryExercises(): array
    {
        return array_map(function (array $e) {
            $cat = $e['category'] ?? null;
            $ins = $e['instructions'] ?? [];
            $isQuiz = is_array($ins) && isset($ins['scenario']);

            return [
                'id'           => $e['id'],
                'title'        => $e['title'],
                'category'     => self::CATEGORIES[$cat] ?? ucfirst((string) $cat),
                'duration_min' => $e['duration_minutes'] ?? null,
                'summary'      => $e['scientific_basis'] ?? null,
                'steps'        => (! $isQuiz && is_array($ins)) ? array_values($ins) : [],
                'quiz'         => $isQuiz ? [
                    'scenario' => $ins['scenario'] ?? null,
                    'question' => $ins['question'] ?? null,
                    'options'  => $ins['options'] ?? [],
                    'correct'  => $ins['correct'] ?? null,
                    'feedback' => $ins['feedback'] ?? null,
                ] : null,
            ];
        }, Data\Exercises::all());
    }

    public function onActivate(): void
    {
        // Création de la table journey_progress
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxilink/database/migrations',
            '--force' => true,
        ]);

        // Seed des exercices
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiLink\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }

    public function slug(): string
    {
        return 'praxilink';
    }
}
