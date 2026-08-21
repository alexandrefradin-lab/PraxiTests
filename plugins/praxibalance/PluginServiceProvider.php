<?php

namespace Praxis\Plugins\PraxiBalance;

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
        // Le contenu (24 notions x 4 formulations) vit dans src/Data/Notions.php :
        // pas de seeder, donc rien à ajouter à deploy-server.sh.
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxibalance/database/migrations',
            '--force' => true,
        ]);
    }
}
