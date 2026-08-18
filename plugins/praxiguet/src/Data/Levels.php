<?php

namespace Praxis\Plugins\PraxiGuet\Data;

/**
 * Les six niveaux de La Tour de Guet.
 *
 * Les niveaux impairs enseignent les mécanismes de l'attention (cartes
 * vrai/faux à ancrer). Les niveaux pairs les mettent à l'épreuve : séries
 * chronométrées où le geste est l'exercice — repérer la cible, freiner un
 * réflexe installé, tenir une consigne qui s'inverse.
 */
class Levels
{
    /** Seuil de validation d'un niveau de connaissance. */
    public const PASS_KNOWLEDGE = 75;

    /** Seuil de validation d'une série chronométrée. */
    public const PASS_TRAINING = 80;

    /** Notions réancrées en ouverture d'une session de connaissance. */
    public const MAX_REVIEW = 4;

    /** Éclats accordés au premier passage validé d'un niveau. */
    public const ECLATS_PER_LEVEL = 25;

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return [
            [
                'id'    => 1,
                'type'  => 'knowledge',
                'title' => "Les mythes de l'attention",
                'desc'  => 'Ce que ton cerveau fait vraiment',
                'rank'  => 'Novice',
            ],
            [
                'id'       => 2,
                'type'     => 'training',
                'title'    => 'Le filtre',
                'desc'     => 'Série chronométrée · 24 stimuli',
                'rank'     => 'Initié',
                'training' => [
                    'count'        => 24,
                    'time_ms'      => 1500,
                    'target_ratio' => 0.72,
                    'palette'      => ['or'],
                    'target'       => ['shape' => 'circle'],
                    'rule'         => 'Glisse à <b>droite</b> uniquement sur les <b>disques</b>.',
                    'labels'       => ['DISQUE', 'AUTRE'],
                    'intro'        => [
                        'icon'  => '⚡',
                        'title' => 'Le filtre',
                        'text'  => "Vite, mais juste. Les disques arrivent souvent : ton geste va devenir automatique — c'est exactement le piège.",
                    ],
                ],
            ],
            [
                'id'    => 3,
                'type'  => 'knowledge',
                'title' => "L'environnement",
                'desc'  => 'Ce qui te vole ton attention',
                'rank'  => 'Adepte',
            ],
            [
                'id'       => 4,
                'type'     => 'training',
                'title'    => 'Inhibition',
                'desc'     => 'Série chronométrée · règle inversée',
                'rank'     => 'Vigile',
                'training' => [
                    'count'           => 24,
                    'time_ms'         => 1400,
                    'target_ratio'    => 0.72,
                    'palette'         => ['or'],
                    'target'          => ['shape' => 'circle'],
                    'invert_at'       => 12,
                    'rule'            => 'Glisse à <b>droite</b> uniquement sur les <b>disques</b>.',
                    'rule_inverted'   => 'Règle inversée : <b>droite</b> sur tout <b>sauf</b> les disques.',
                    'labels'          => ['DISQUE', 'AUTRE'],
                    'labels_inverted' => ['AUTRE', 'DISQUE'],
                    'intro'           => [
                        'icon'  => '🔄',
                        'title' => 'Inhibition',
                        'text'  => "Même règle qu'au niveau 2… jusqu'à ce qu'elle s'inverse en cours de route. Il faudra freiner un réflexe déjà installé.",
                    ],
                ],
            ],
            [
                'id'    => 5,
                'type'  => 'knowledge',
                'title' => 'Énergie et vagabondage',
                'desc'  => 'Gérer son carburant mental',
                'rank'  => 'Sentinelle',
            ],
            [
                'id'       => 6,
                'type'     => 'training',
                'title'    => 'Attention soutenue',
                'desc'     => 'Série chronométrée · double critère',
                'rank'     => "Maître de l'Ancrage",
                'training' => [
                    'count'        => 28,
                    'time_ms'      => 1300,
                    'target_ratio' => 0.45,
                    'palette'      => ['or', 'cramoisi'],
                    'target'       => ['shape' => 'circle', 'color' => 'or'],
                    'rule'         => "Glisse à <b>droite</b> seulement si c'est un <b>disque ET or</b>.",
                    'labels'       => ['DISQUE OR', 'AUTRE'],
                    'intro'        => [
                        'icon'  => '🎯',
                        'title' => 'Attention soutenue',
                        'text'  => "Deux critères à tenir en même temps, sur une série plus longue. C'est la fin de série qui trahit la fatigue attentionnelle.",
                    ],
                ],
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        foreach (self::all() as $level) {
            if ($level['id'] === $id) {
                return $level;
            }
        }

        return null;
    }

    /** Seuil de validation applicable à un niveau. */
    public static function passMark(array $level): int
    {
        return $level['type'] === 'knowledge'
            ? self::PASS_KNOWLEDGE
            : self::PASS_TRAINING;
    }

    /** Rang atteint après N niveaux validés. */
    public static function rankFor(int $completedCount): string
    {
        if ($completedCount < 1) {
            return 'Profane';
        }

        $levels = self::all();
        $index  = min($completedCount, count($levels)) - 1;

        return $levels[$index]['rank'];
    }
}
