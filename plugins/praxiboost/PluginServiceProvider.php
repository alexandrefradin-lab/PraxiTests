<?php

namespace Praxis\Plugins\PraxiBoost;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Plugins\PraxiBoost\Services\ExerciseUnlocker;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        // Déblocage par paliers : à chaque gain d'Éclats, on vérifie si de
        // nouveaux exercices passent au-dessus de leur seuil pour cet utilisateur.
        $this->registerActions([
            'gamification.xp_awarded' => function ($user, $amount, $reason, $progress) {
                app(ExerciseUnlocker::class)->syncFor($user);
            },
        ]);
    }

    public function onActivate(): void
    {
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxiboost/database/migrations',
            '--force' => true,
        ]);

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiBoost\\Database\\Seeders\\DevExercisesSeeder',
            '--force' => true,
        ]);
    }
}
