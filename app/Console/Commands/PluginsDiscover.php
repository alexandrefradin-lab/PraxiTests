<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Praxis\Core\Plugins\PluginRegistry;

class PluginsDiscover extends Command
{
    protected $signature = 'praxitests:plugins:discover {--sync : Synchroniser avec la base}';
    protected $description = 'Découvre les plugins dans /plugins et synchronise leurs manifests';

    public function handle(PluginRegistry $registry): int
    {
        $manifests = $registry->discover(true);
        $this->info("Plugins détectés : {$manifests->count()}");
        foreach ($manifests as $m) {
            $this->line("  - {$m['slug']} v{$m['version']} ({$m['type']})");
        }

        if ($this->option('sync')) {
            $sync = $registry->syncToDatabase();
            $this->info('Sync DB :');
            foreach (['installed', 'updated', 'removed'] as $key) {
                $this->line("  {$key} : " . implode(', ', $sync[$key] ?: ['—']));
            }
        }

        return self::SUCCESS;
    }
}
