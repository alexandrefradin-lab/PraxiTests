<?php

namespace Praxis\Core\Plugins\Contracts;

interface PluginContract
{
    /** Slug unique du plugin */
    public function slug(): string;

    /** Nom affiché */
    public function name(): string;

    /** Version sémantique */
    public function version(): string;

    /** Type : test, scoring, ai, mail, gamification, integration, theme, reporting */
    public function type(): string;

    /** Hook : appelé à l'activation du plugin */
    public function onActivate(): void;

    /** Hook : appelé à la désactivation */
    public function onDeactivate(): void;

    /** Hook : appelé à l'install (migrations, seeds) */
    public function onInstall(): void;

    /** Hook : appelé à la désinstallation (cleanup) */
    public function onUninstall(): void;

    /** Capabilities/permissions demandées */
    public function permissions(): array;

    /** Manifest du plugin (lecture plugin.json) */
    public function manifest(): array;
}
