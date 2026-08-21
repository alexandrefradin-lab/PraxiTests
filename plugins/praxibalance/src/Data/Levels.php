<?php

namespace Praxis\Plugins\PraxiBalance\Data;

/**
 * Les sept niveaux de La Balance.
 *
 * Les niveaux impairs enseignent les mécanismes de l'arbitrage (cartes
 * vrai/faux à ancrer). Les niveaux pairs les mettent à l'épreuve : des tâches
 * réelles défilent, chronomètre en marche, et il faut trancher — sur
 * l'importance, puis avec une consigne qui bascule en cours de série, enfin
 * sur deux critères tenus à la fois.
 *
 * Le critère de tri est décrit par `criteria` : la carte part à droite quand
 * TOUS les attributs listés sont vrais. Attributs d'une tâche :
 *   u  urgent            — ça réclame maintenant
 *   i  important         — ça pèse sur ce qui compte
 *   m  de mon ressort    — c'est bien à moi de le faire
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
                'title' => "Urgent n'est pas important",
                'desc'  => 'Deux mesures qu\'on confond tout le temps',
                'rank'  => 'Apprenti',
            ],
            [
                'id'       => 2,
                'type'     => 'training',
                'title'    => 'Le tri',
                'desc'     => 'Série chronométrée · 24 tâches',
                'rank'     => 'Trieur',
                'training' => [
                    'count'        => 24,
                    'time_ms'      => 4000,
                    'target_ratio' => 0.50,
                    'criteria'     => ['i'],
                    'rule'         => "Glisse à <b>droite</b> ce qui est <b>important</b>, à gauche le reste.",
                    'labels'       => ['IMPORTANT', 'PAS IMPORTANT'],
                    'intro'        => [
                        'icon'  => '⚖️',
                        'title' => 'Le tri',
                        'text'  => "Une tâche, quelques secondes pour décider. Ne te demande pas si ça presse : demande-toi ce qui arrive si ce n'est pas fait.",
                    ],
                ],
            ],
            [
                'id'    => 3,
                'type'  => 'knowledge',
                'title' => 'Le coût de dire oui',
                'desc'  => 'Refuser, déléguer, finir',
                'rank'  => 'Arbitre',
            ],
            [
                'id'       => 4,
                'type'     => 'training',
                'title'    => 'Le renversement',
                'desc'     => 'Série chronométrée · la consigne bascule',
                'rank'     => 'Juge',
                'training' => [
                    'count'           => 24,
                    'time_ms'         => 3600,
                    'target_ratio'    => 0.50,
                    'criteria'        => ['u'],
                    'invert_at'       => 12,
                    'criteria_after'  => ['i'],
                    'rule'            => "Glisse à <b>droite</b> ce qui est <b>urgent</b>.",
                    'rule_inverted'   => "La consigne change : glisse à <b>droite</b> ce qui est <b>important</b>.",
                    'labels'          => ['URGENT', 'PAS URGENT'],
                    'labels_inverted' => ['IMPORTANT', 'PAS IMPORTANT'],
                    'intro'           => [
                        'icon'  => '🔄',
                        'title' => 'Le renversement',
                        'text'  => "D'abord l'urgence, ce qui est facile. Puis la consigne bascule vers l'importance — et tu verras que ce ne sont pas du tout les mêmes cartes.",
                    ],
                ],
            ],
            [
                'id'    => 5,
                'type'  => 'knowledge',
                'title' => "Le sens de l'effort",
                'desc'  => 'Impact, estimation, renoncement',
                'rank'  => 'Stratège',
            ],
            [
                'id'       => 6,
                'type'     => 'training',
                'title'    => 'Deux critères',
                'desc'     => 'Série chronométrée · important ET de ton ressort',
                'rank'     => 'Gardien du Fléau',
                'training' => [
                    'count'        => 28,
                    'time_ms'      => 3400,
                    'target_ratio' => 0.42,
                    'criteria'     => ['i', 'm'],
                    'rule'         => "Glisse à <b>droite</b> seulement si c'est <b>important ET de ton ressort</b>.",
                    'labels'       => ['À MOI', 'PAS À MOI'],
                    'intro'        => [
                        'icon'  => '🎯',
                        'title' => 'Deux critères',
                        'text'  => "Attention au piège : certaines cartes sont vraiment importantes, mais ce n'est pas à toi de les traiter. Ce sont celles-là qui remplissent les journées.",
                    ],
                ],
            ],
            [
                'id'    => 7,
                'type'  => 'knowledge',
                'title' => 'Arbitrer avec les autres',
                'desc'  => 'Négocier, rendre visible, alerter tôt',
                'rank'  => 'Maître de la Balance',
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
