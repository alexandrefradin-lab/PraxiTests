<?php

namespace Praxis\Core\Plugins;

/**
 * Système d'événements style WordPress (actions + filters).
 *
 * Action  : déclenche des side-effects (notifications, log, mail, ...)
 * Filter  : permet de transformer une valeur avant son utilisation
 */
class PluginHooks
{
    /** @var array<string, array<int, array<int, callable>>> */
    protected static array $actions = [];

    /** @var array<string, array<int, array<int, callable>>> */
    protected static array $filters = [];

    public static function action(string $event, callable $handler, int $priority = 10): void
    {
        self::$actions[$event][$priority][] = $handler;
    }

    public static function filter(string $event, callable $handler, int $priority = 10): void
    {
        self::$filters[$event][$priority][] = $handler;
    }

    public static function doAction(string $event, mixed ...$args): void
    {
        if (!isset(self::$actions[$event])) {
            return;
        }
        ksort(self::$actions[$event]);
        foreach (self::$actions[$event] as $handlers) {
            foreach ($handlers as $handler) {
                $handler(...$args);
            }
        }
    }

    public static function applyFilters(string $event, mixed $value, mixed ...$args): mixed
    {
        if (!isset(self::$filters[$event])) {
            return $value;
        }
        ksort(self::$filters[$event]);
        foreach (self::$filters[$event] as $handlers) {
            foreach ($handlers as $handler) {
                $value = $handler($value, ...$args);
            }
        }
        return $value;
    }

    public static function hasAction(string $event): bool
    {
        return !empty(self::$actions[$event]);
    }

    public static function hasFilter(string $event): bool
    {
        return !empty(self::$filters[$event]);
    }

    public static function reset(): void
    {
        self::$actions = [];
        self::$filters = [];
    }
}
