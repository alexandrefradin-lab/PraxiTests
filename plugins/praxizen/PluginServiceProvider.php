<?php

namespace Praxis\Plugins\PraxiZen;

use Praxis\Core\Library\ExerciseLibrary;
use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    private const CATEGORIES = [
        'respiration' => 'Respiration',
        'mindfulness' => 'Pleine conscience',
        'cognitif'    => 'Recadrage cognitif',
        'corporel'    => 'Détente corporelle',
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadViewsFrom($this->pluginPath('resources/views'), $this->slug());
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiZenScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxizen-stress'
                    ? 'PraxiZenResult'
                    : $page,
        ]);

        // Salle du Trésor : bibliothèque d'exercices (plus de test à l'entrée).
        app(ExerciseLibrary::class)->register('praxizen', [
            'title'     => 'Le Refuge Intérieur',
            'subtitle'  => 'Des exercices guidés pour apaiser le mental : respiration, ancrage, recadrage.',
            'icon'      => 'ti-yin-yang',
            'exercises' => fn () => self::libraryExercises(),
        ]);
    }

    /** Catalogue normalisé pour la bibliothèque d'exercices. */
    private static function libraryExercises(): array
    {
        return array_map(function (array $e) {
            $cat = $e['category'] ?? null;

            return [
                'id'           => $e['id'],
                'title'        => $e['title'],
                'category'     => self::CATEGORIES[$cat] ?? ucfirst((string) $cat),
                'duration_min' => $e['duration_minutes'] ?? null,
                'summary'      => $e['scientific_basis'] ?? null,
                'steps'        => $e['instructions'] ?? [],
            ];
        }, Data\Exercises::all());
    }

    public function onActivate(): void
    {
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxizen/database/migrations',
            '--force' => true,
        ]);

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiZen\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }
}
