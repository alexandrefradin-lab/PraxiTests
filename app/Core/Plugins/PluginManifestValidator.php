<?php

namespace Praxis\Core\Plugins;

use InvalidArgumentException;

class PluginManifestValidator
{
    protected static array $required = ['slug', 'name', 'version', 'type', 'service_provider'];

    public static function validate(array $manifest, string $sourcePath = ''): void
    {
        foreach (self::$required as $key) {
            if (empty($manifest[$key])) {
                throw new InvalidArgumentException("Plugin manifest missing required key '{$key}' [{$sourcePath}]");
            }
        }

        $allowedTypes = config('plugins.available_types', []);
        if ($allowedTypes && !in_array($manifest['type'], $allowedTypes, true)) {
            throw new InvalidArgumentException("Plugin type '{$manifest['type']}' not allowed [{$sourcePath}]");
        }

        if (!preg_match('/^[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?$/', $manifest['slug'])) {
            throw new InvalidArgumentException("Plugin slug invalid: '{$manifest['slug']}'");
        }

        if (!preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.\-]+)?$/', $manifest['version'])) {
            throw new InvalidArgumentException("Plugin version not semver: '{$manifest['version']}'");
        }

        $allowedPerms = config('plugins.available_permissions', []);
        foreach ($manifest['permissions'] ?? [] as $perm) {
            if ($allowedPerms && !in_array($perm, $allowedPerms, true)) {
                throw new InvalidArgumentException("Permission '{$perm}' not allowed");
            }
        }
    }
}
