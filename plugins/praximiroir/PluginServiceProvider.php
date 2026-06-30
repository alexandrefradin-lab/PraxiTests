<?php

namespace Praxis\Plugins\PraxiMiroir;

use Praxis\Core\Plugins\AbstractPlugin;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));
    }

    public function onActivate(): void
    {
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praximiroir/database/migrations',
            '--force' => true,
        ]);

        // Le seeder est hors de src/ donc pas dans le PSR-4 du PluginManager.
        require_once __DIR__ . '/database/seeders/MirrorExercisesSeeder.php';

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiMiroir\\Database\\Seeders\\MirrorExercisesSeeder',
            '--force' => true,
        ]);
    }
}
