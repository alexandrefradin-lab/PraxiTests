<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Praxis\Core\Plugins\PluginManifestValidator;

/**
 * Valide tous les manifests plugin.json présents dans plugins/.
 *
 * Usage :
 *   php artisan plugins:validate          # tous les plugins
 *   php artisan plugins:validate praxilink # un seul
 *
 * Exit code 0 = tout OK, 1 = au moins une erreur (idéal pour CI).
 */
class PluginsValidate extends Command
{
    protected $signature   = 'plugins:validate {slug? : Slug d\'un plugin spécifique}';
    protected $description = 'Valide les manifests plugin.json (type, namespace, service_provider, semver…)';

    public function handle(): int
    {
        $pluginsPath = base_path('plugins');

        if (!is_dir($pluginsPath)) {
            $this->error("Dossier plugins/ introuvable : {$pluginsPath}");
            return self::FAILURE;
        }

        $slug = $this->argument('slug');

        $dirs = $slug
            ? ["{$pluginsPath}/{$slug}"]
            : glob("{$pluginsPath}/*/", GLOB_ONLYDIR);

        if (empty($dirs)) {
            $this->warn('Aucun dossier plugin trouvé.');
            return self::SUCCESS;
        }

        $errors  = 0;
        $skipped = 0;
        $ok      = 0;

        foreach ($dirs as $dir) {
            $dir      = rtrim($dir, '/');
            $dirName  = basename($dir);
            $manifest = "{$dir}/plugin.json";

            // Ignorer les dossiers système
            if (in_array($dirName, ['_template', '_wp_import'], true)) {
                $skipped++;
                continue;
            }

            if (!file_exists($manifest)) {
                $this->warn("  [SKIP] {$dirName} — plugin.json absent");
                $skipped++;
                continue;
            }

            $data = json_decode(file_get_contents($manifest), true);

            if (!is_array($data)) {
                $this->error("  [FAIL] {$dirName} — plugin.json invalide (JSON malformé)");
                $errors++;
                continue;
            }

            try {
                PluginManifestValidator::validate($data, $manifest);
                $this->line("  <info>[OK]</info>   {$dirName} — {$data['name']} v{$data['version']}");
                $ok++;
            } catch (\InvalidArgumentException $e) {
                $this->error("  [FAIL] {$dirName} — {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->line("Résultat : <info>{$ok} OK</info>, <comment>{$skipped} ignorés</comment>, <error>{$errors} erreur(s)</error>");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
