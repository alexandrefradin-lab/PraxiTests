<?php

namespace Praxis\Plugins\PraxiFlow\Data;

/**
 * Adapte le contenu du parcours PraxiFlow (Journey::days) à la forme normalisée
 * attendue par le moteur mutualisé (JourneyRegistry / JourneyDashboardController).
 */
class JourneyAdapter
{
    private const PHASE_ICON = [
        'decouverte'   => 'map',
        'installation' => 'anchor',
        'renforcement' => 'shield',
        'maitrise'     => 'target',
    ];

    /** @return array<int,array> */
    public static function days(): array
    {
        return array_map(static function (array $d): array {
            return [
                'day'             => (int) ($d['day'] ?? 0),
                'theme'           => $d['weekly_theme'] ?? ('Semaine ' . ($d['week'] ?? '')),
                'title'           => $d['title'] ?? '',
                'summary'         => $d['intention'] ?? '',
                'body'            => self::body($d),
                'micro_challenge' => $d['micro_habit'] ?? '',
                'duration_min'    => (int) ($d['duration_minutes'] ?? 5),
                'icon'            => self::PHASE_ICON[$d['phase'] ?? ''] ?? 'clock',
            ];
        }, Journey::days());
    }

    private static function body(array $d): string
    {
        $parts = [];

        if (! empty($d['anchor'])) {
            $parts[] = "## Quand le faire\n" . $d['anchor'];
        }
        if (! empty($d['tip_science'])) {
            $parts[] = "## Pourquoi ça marche\n" . $d['tip_science'];
        }
        if (! empty($d['reward'])) {
            $parts[] = "## Ce que ça t'apporte\n" . $d['reward'];
        }

        return implode("\n\n", $parts);
    }
}
