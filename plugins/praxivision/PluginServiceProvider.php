<?php

namespace Praxis\Plugins\PraxiVision;

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
            '--path'  => 'plugins/praxivision/database/migrations',
            '--force' => true,
        ]);

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiVision\\Database\\Seeders\\VisionPracticesSeeder',
            '--force' => true,
        ]);
    }
}
