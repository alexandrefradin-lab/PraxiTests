<?php

namespace Praxis\Core\AI;

use InvalidArgumentException;
use Praxis\Core\AI\Contracts\AIDriverContract;
use Praxis\Core\Plugins\PluginHooks;

class AIManager
{
    /** @var array<string, AIDriverContract> */
    protected array $instances = [];

    public function driver(?string $name = null, ?string $modelOverride = null): AIDriverContract
    {
        $name ??= config('ai.default');

        // La clé de cache inclut l'éventuel modèle surchargé : un même driver peut
        // ainsi servir deux tâches avec deux modèles différents (ex. Sonnet vs Haiku).
        $cacheKey = $modelOverride ? "{$name}@{$modelOverride}" : $name;

        if (isset($this->instances[$cacheKey])) {
            return $this->instances[$cacheKey];
        }

        $config = config("ai.drivers.{$name}");
        if (!$config) {
            throw new InvalidArgumentException("AI driver not configured: {$name}");
        }

        // Override de modèle par tâche (réglage admin) : on clone la config du driver
        // en remplaçant juste le modèle, sans toucher aux autres usages du driver.
        if ($modelOverride) {
            $config['model'] = $modelOverride;
        }

        $driverClass = $config['driver'] ?? null;

        // Sécurité (cf. audit E-2) : on n'instancie jamais une classe arbitraire.
        // Le driver doit exister et implémenter le contrat attendu.
        if (!is_string($driverClass) || !class_exists($driverClass)
            || !is_subclass_of($driverClass, AIDriverContract::class)) {
            throw new InvalidArgumentException("Invalid AI driver class for: {$name}");
        }

        $instance = new $driverClass($config);

        // Permettre aux plugins de remplacer un driver
        $instance = PluginHooks::applyFilters("ai.driver.{$name}", $instance, $config);

        return $this->instances[$cacheKey] = $instance;
    }

    public function forTask(string $task): AIDriverContract
    {
        $driver = config("ai.tasks.{$task}.driver");
        // Modèle spécifique à la tâche (réglage admin) : prime sur le modèle du driver.
        $model  = config("ai.tasks.{$task}.model");
        return $this->driver($driver, is_string($model) && $model !== '' ? $model : null);
    }
}
