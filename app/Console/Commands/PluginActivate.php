<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Praxis\Core\Plugins\PluginManager;

class PluginActivate extends Command
{
    protected $signature = 'praxitests:plugins:activate {slug}';
    protected $description = 'Active un plugin';

    public function handle(PluginManager $manager): int
    {
        $manager->activate($this->argument('slug'));
        $this->info("Plugin {$this->argument('slug')} activé.");
        return self::SUCCESS;
    }
}
