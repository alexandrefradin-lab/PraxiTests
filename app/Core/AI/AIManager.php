<?php

namespace Praxis\Core\AI;

use InvalidArgumentException;
use Praxis\Core\AI\Contracts\AIDriverContract;
use Praxis\Core\Plugins\PluginHooks;

class AIManager
{
    /** @var array<string, AIDriverContract> */
    protected array $instances = [];

    public function driver(?string $name = null): AIDriverContract
    {
        $name ??= config('ai.default');

        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $config = config("ai.drivers.{$name}");
        if (!$config) {
            throw new InvalidArgumentException("AI driver not configured: {$name}");
        }

        $driverClass = $config['driver'] ?? null;

        // Sécurité (cf. audit E-2) : on n'instancie jamais une classe arbitraire.
        // Le driver doit exister et implémenter le contrat attendu.
        if (!is_string($driverClass) || !class_exists($driverClass)
            || !is_subclass_of($driverClass, AIDriverContract::class)) {
            throw new InvalidArgumentException("Invalid AI driver class for: {$name}");
        }

        $driverClass = $config['driver'];
        $instance = new $driverClass($config);

        // Permettre aux plugins de remplacer un driver
        $instance = PluginHooks::applyFilters("ai.driver.{$name}", $instance, $config);

        return $this->instances[$name] = $instance;
    }

    public function forTask(string $task): AIDriverContract
    {
        $driver = config("ai.tasks.{$task}.driver");
        return $this->driver($driver);
    }
}
