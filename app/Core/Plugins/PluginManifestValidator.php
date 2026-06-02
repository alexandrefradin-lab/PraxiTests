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

        // SEC-05 : service_provider doit être un FQCN, et rester dans le namespace déclaré du plugin
        $sp = $manifest['service_provider'] ?? '';
        if (!preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]+$/', $sp)) {
            throw new InvalidArgumentException("Invalid service_provider: {$sp}");
        }
        $ns = trim($manifest['namespace'] ?? '', '\\');
        if ($ns && !str_starts_with($sp, $ns . '\\')) {
            throw new InvalidArgumentException("service_provider must be within namespace {$ns}");
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
