<?php

namespace Praxis\Core\Journey;

/**
 * Registre des parcours 60 jours. Chaque plugin de type parcours s'y déclare
 * (dans son PluginServiceProvider::boot) avec son identité et son contenu déjà
 * NORMALISÉ à la forme attendue par le tableau de bord générique.
 *
 * Forme d'un jour attendue :
 *   [
 *     'day'          => int,      // 1..60
 *     'theme'        => string,   // regroupement (semaine/phase/bloc)
 *     'title'        => string,
 *     'summary'      => string,   // phrase courte (carte du jour)
 *     'body'         => string,   // contenu détaillé (page pratique)
 *     'micro_challenge' => string,// le geste concret du jour
 *     'duration_min' => int,
 *     'icon'         => string,   // clé d'icône (mappée vers tabler côté front)
 *   ]
 *
 * Config d'un parcours :
 *   [
 *     'title'    => string,   // nom poétique
 *     'subtitle' => string,   // « … - 60 jours »
 *     'color'    => string,   // accent (hex) — défaut charte or
 *     'days'     => array,    // liste des jours normalisés (60)
 *   ]
 */
class JourneyRegistry
{
    /** @var array<string, array> */
    protected static array $journeys = [];

    public static function register(string $slug, array $config): void
    {
        static::$journeys[$slug] = $config;
    }

    public static function get(string $slug): ?array
    {
        return static::$journeys[$slug] ?? null;
    }

    public static function has(string $slug): bool
    {
        return isset(static::$journeys[$slug]);
    }

    /** @return array<int,string> */
    public static function slugs(): array
    {
        return array_keys(static::$journeys);
    }

    /**
     * Jours normalisés d'un parcours. `days` peut être un tableau OU un callable
     * (résolution paresseuse : on ne mappe pas 60 entrées à chaque requête).
     */
    public static function days(string $slug): array
    {
        $cfg = static::$journeys[$slug] ?? null;
        if (! $cfg) {
            return [];
        }

        $days = $cfg['days'] ?? [];

        return is_callable($days) ? $days() : $days;
    }

    /** Retrouve un jour précis dans le contenu d'un parcours. */
    public static function day(string $slug, int $day): ?array
    {
        foreach (static::days($slug) as $entry) {
            if ((int) ($entry['day'] ?? 0) === $day) {
                return $entry;
            }
        }

        return null;
    }
}
