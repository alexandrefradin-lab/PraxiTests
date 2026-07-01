<?php

namespace Praxis\Plugins\PraxiFlow;

use Praxis\Core\Journey\JourneyRegistry;
use Praxis\Core\Library\ExerciseLibrary;
use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiFlowScoringEngine());

        $this->registerFilters([
            'results.inertia_page' => fn (string $page, $attempt) =>
                $attempt->test->scoring_engine === 'praxiflow-scoring' ? 'PraxiFlowResult' : $page,
        ]);

        // Parcours 60 jours (moteur mutualisé). Contenu résolu paresseusement.
        JourneyRegistry::register('praxiflow', [
            'title'    => 'Le Maître du Temps',
            'subtitle' => 'Gestion du temps - 60 jours',
            'color'    => '#B87A1A',
            'days'     => fn () => \Praxis\Core\Journey\WeeklyPhaseAdapter::adapt(Data\Journey::days()),
        ]);

        // Salle du Trésor : bibliothèque d'exercices (plus de test à l'entrée).
        app(ExerciseLibrary::class)->register('praxiflow', [
            'title'     => 'Le Maître du Temps',
            'subtitle'  => 'Des exercices guidés pour dompter ta productivité : focus, priorités, anti-procrastination.',
            'icon'      => 'ti-hourglass-high',
            'exercises' => fn () => self::libraryExercises(),
            'tips'      => fn () => Data\Tips::all(),
        ]);
    }

    /** Catalogue normalisé pour la bibliothèque d'exercices. */
    private static function libraryExercises(): array
    {
        $dims = Data\Exercises::dimensions();

        return array_map(function (array $e) use ($dims) {
            $cat = $e['category'] ?? null;

            return [
                'id'           => $e['id'],
                'title'        => $e['title'],
                'category'     => $dims[$cat]['label'] ?? ucfirst(str_replace('_', ' ', (string) $cat)),
                'duration_min' => $e['duration_minutes'] ?? null,
                'summary'      => $e['scientific_basis'] ?? null,
                'steps'        => $e['instructions'] ?? [],
                'icon'         => $dims[$cat]['icon'] ?? null,
            ];
        }, Data\Exercises::exercises());
    }

    public function onActivate(): void
    {
        // Migrations
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxiflow/database/migrations',
            '--force' => true,
        ]);

        // Seeders
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiFlow\\Database\\Seeders\\ExercisesSeeder',
            '--force' => true,
        ]);
    }
}
