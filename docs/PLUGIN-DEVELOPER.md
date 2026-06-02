# Développer un plugin PraxiQuest

## Structure minimum

```
plugins/mon-plugin/
├── plugin.json
├── PluginServiceProvider.php
├── src/
│   └── ...
├── resources/
│   ├── views/
│   └── js/
├── routes/
│   └── plugin.php
└── database/
    └── migrations/
```

## plugin.json

```json
{
  "slug": "mon-plugin",
  "name": "Mon Plugin",
  "version": "1.0.0",
  "author": "Toi",
  "type": "test",
  "description": "Description courte.",
  "namespace": "Praxis\\MonPlugin",
  "service_provider": "Praxis\\MonPlugin\\PluginServiceProvider",
  "permissions": ["read:profiles", "write:results"],
  "requires": { "praxiquest": ">=1.0.0" }
}
```

Types valides : `test`, `scoring`, `ai`, `mail`, `gamification`, `integration`, `theme`, `reporting`.

## ServiceProvider

```php
<?php

namespace Praxis\MonPlugin;

use Praxis\Core\Plugins\AbstractPlugin;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void
    {
        // Bind services
    }

    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadViewsFrom($this->pluginPath('resources/views'), $this->slug());
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->registerActions([
            'attempt.completed' => [$this, 'onAttemptCompleted'],
        ]);

        $this->registerFilters([
            'jobs.suggested' => [$this, 'enrichJobs'],
        ]);
    }

    public function onAttemptCompleted($attempt): void
    {
        // ...
    }

    public function enrichJobs(array $jobs, $attempt): array
    {
        return $jobs;
    }

    public function onActivate(): void
    {
        // Migration plugin, config par défaut, etc.
    }
}
```

## Hooks disponibles

### Actions (effets de bord)

| Event | Args |
|-------|------|
| `plugin.booted` | `slug, provider` |
| `plugin.activated` | `slug` |
| `plugin.deactivated` | `slug` |
| `profile.completed` | `Profile` |
| `attempt.started` | `TestAttempt` |
| `attempt.answered` | `TestAttempt, questionId, value` |
| `attempt.completed` | `TestAttempt` |
| `ai.synthesis.completed` | `TestAttempt, text` |
| `jobs.generated` | `TestAttempt, jobs[]` |
| `gamification.xp_awarded` | `User, amount, reason, progress` |
| `gamification.badge_earned` | `User, Badge` |
| `gamification.insight_unlocked` | `User, Test, key, payload` |
| `campaign.sent` | `campaign, stats` |
| `insights.generated` | `TestAttempt` |

### Filters (transformation de valeur)

| Event | Default value |
|-------|---------------|
| `attempt.scoring` | `array $scoring` |
| `jobs.suggested` | `array $jobs` |
| `ai.synthesis.messages` | `array $messages` |
| `ai.synthesis.output` | `string $text` |
| `ai.driver.{name}` | `AIDriverContract $driver` |
| `email.context` | `array $context` |
| `gamification.badge.criterion` | `bool $matches` |

## Enregistrer un type de test

Dans `boot()` :

```php
$this->app->make(\Praxis\Core\TestEngine\TestEngine::class)
    ->registerScoringEngine(new \Praxis\MonPlugin\Scoring\MbtiScoringEngine());
```

## Enregistrer un driver IA

```php
\Praxis\Core\Plugins\PluginHooks::filter('ai.driver.openai', function ($driver, $config) {
    return new \Praxis\MonPlugin\AI\CustomOpenAi($config);
});
```

## Permissions

Demande dans `plugin.json` uniquement les permissions strictement nécessaires :

| Permission | Effet |
|------------|-------|
| `read:profiles` | Accès lecture aux profils utilisateur |
| `write:profiles` | Modification profils |
| `read:tests` | Lecture tests |
| `write:tests` | Création / édition tests |
| `read:results` | Lecture résultats |
| `write:results` | Écriture résultats / scoring |
| `send:mail` | Envoi d'emails |
| `manage:plugins` | Gestion d'autres plugins |
| `access:admin` | Pages admin |

## CLI

```bash
php artisan praxiquest:plugins:discover         # Liste les plugins du disque
php artisan praxiquest:plugins:discover --sync  # Synchronise avec la DB
php artisan praxiquest:plugins:activate <slug>
```

## Distribution

Un plugin distribuable = un dossier zippé contenant :
- `plugin.json`
- `PluginServiceProvider.php`
- `src/`
- `composer.json` (optionnel, pour vendor isolé)

L'installeur de plugins (à venir) extraira l'archive dans `plugins/` et lancera la sync.
