<?php

namespace Praxis\Plugins\PraxiLead;

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
        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('migrate', [
            '--path'  => 'plugins/praxilead/database/migrations',
            '--force' => true,
        ]);

        // TODO ARC-M1: Artisan::call() dans onActivate() bloque la requête HTTP.
        // Déplacer vers une commande CLI onInstall() ou un job dispatchable en arrière-plan.
        // Voir documentation PraxiQuest Architecture > Plugin Lifecycle.
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiLead\\Database\\Seeders\\MgmtPracticesSeeder',
            '--force' => true,
        ]);
    }
}
