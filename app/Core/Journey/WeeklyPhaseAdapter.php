<?php

namespace Praxis\Core\Journey;

/**
 * Adaptateur mutualisé pour les parcours structurés par jour / semaine / phase
 * (PraxiFlow, PraxiLink, PraxiSelf, PraxiSpeak, PraxiZen partagent cette forme).
 *
 * Entrée : liste de jours à clés
 *   day, week, phase, title, exercise_ref, duration_minutes,
 *   anchor, intention, micro_habit, reward, weekly_theme, tip_science
 *
 * Sortie : forme normalisée attendue par le tableau de bord générique.
 */
class WeeklyPhaseAdapter
{
    private const PHASE_ICON = [
        'decouverte'   => 'compass',
        'installation' => 'anchor',
        'renforcement' => 'shield',
        'maitrise'     => 'target',
    ];

    /**
     * @param  array<int,array>  $days
     * @return array<int,array>
     */
    public static function adapt(array $days): array
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
                'icon'            => self::PHASE_ICON[$d['phase'] ?? ''] ?? 'seedling',
            ];
        }, $days);
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
