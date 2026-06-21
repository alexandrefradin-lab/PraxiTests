<?php

namespace Praxis\Plugins\PraxiTempo\Data;

/**
 * Items d'auto-évaluation de la gestion du temps (PraxiTempo).
 *
 * 16 affirmations (4 par dimension), échelle 1-5 :
 *   1 = Pas du tout — 5 = Tout à fait.
 *
 * Items inversés (`reversed => true`) : recodés 6 - valeur par le ScoringEngine,
 * pour neutraliser le biais d'acquiescement.
 *
 * Les 4 clés de dimension correspondent exactement à
 * PraxiTempoScoringEngine::dimensions() / Questions::dimensions().
 */
class Questions
{
    public static function all(): array
    {
        $scale = ['min' => 1, 'max' => 5, 'min_label' => 'Pas du tout', 'max_label' => 'Tout à fait'];

        return [
            // ── Priorisation ─────────────────────────────────────────────
            [
                'section' => 'Priorisation',
                'prompt'  => "Avant de me lancer, je sais dire quelles tâches comptent vraiment.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'priorisation', 'weight' => 1],
            ],
            [
                'section' => 'Priorisation',
                'prompt'  => "Je me laisse happer par l'urgent au détriment de l'important.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'priorisation', 'weight' => 1, 'reversed' => true],
            ],
            [
                'section' => 'Priorisation',
                'prompt'  => "Quand tout semble prioritaire, j'arrive à trancher.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'priorisation', 'weight' => 1],
            ],
            [
                'section' => 'Priorisation',
                'prompt'  => "Je termine mes journées en ayant avancé sur l'essentiel.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'priorisation', 'weight' => 1],
            ],

            // ── Planification ────────────────────────────────────────────
            [
                'section' => 'Planification',
                'prompt'  => "Je planifie mes journées ou mes semaines à l'avance.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'planification', 'weight' => 1],
            ],
            [
                'section' => 'Planification',
                'prompt'  => "J'estime plutôt bien le temps qu'une tâche va me prendre.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'planification', 'weight' => 1],
            ],
            [
                'section' => 'Planification',
                'prompt'  => "Je découpe les gros projets en étapes concrètes.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'planification', 'weight' => 1],
            ],
            [
                'section' => 'Planification',
                'prompt'  => "Je me retrouve souvent à improviser faute d'avoir anticipé.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'planification', 'weight' => 1, 'reversed' => true],
            ],

            // ── Focus & interruptions ────────────────────────────────────
            [
                'section' => 'Focus & interruptions',
                'prompt'  => "Une fois lancé(e), je reste concentré(e) jusqu'au bout.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'focus', 'weight' => 1],
            ],
            [
                'section' => 'Focus & interruptions',
                'prompt'  => "Je repousse les tâches désagréables même quand elles sont importantes.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'focus', 'weight' => 1, 'reversed' => true],
            ],
            [
                'section' => 'Focus & interruptions',
                'prompt'  => "Je sais couper notifications et distractions pour avancer.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'focus', 'weight' => 1],
            ],
            [
                'section' => 'Focus & interruptions',
                'prompt'  => "Je passe d'une tâche à l'autre sans en finir aucune.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'focus', 'weight' => 1, 'reversed' => true],
            ],

            // ── Équilibre & énergie ──────────────────────────────────────
            [
                'section' => 'Équilibre & énergie',
                'prompt'  => "Je m'accorde de vraies pauses sans culpabiliser.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'equilibre', 'weight' => 1],
            ],
            [
                'section' => 'Équilibre & énergie',
                'prompt'  => "Je cale mes tâches exigeantes sur mes moments de meilleure énergie.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'equilibre', 'weight' => 1],
            ],
            [
                'section' => 'Équilibre & énergie',
                'prompt'  => "Je finis souvent mes journées épuisé(e) et débordé(e).",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'equilibre', 'weight' => 1, 'reversed' => true],
            ],
            [
                'section' => 'Équilibre & énergie',
                'prompt'  => "Je sais dire non ou déléguer quand ma charge déborde.",
                'type'    => 'scale',
                'options' => $scale,
                'scoring' => ['dimension' => 'equilibre', 'weight' => 1],
            ],
        ];
    }

    /**
     * Métadonnées des dimensions — libellés, descriptions, couleurs et conseils.
     */
    public static function dimensions(): array
    {
        return [
            'priorisation' => [
                'label'       => 'Priorisation',
                'description' => "Ta capacité à distinguer l'important de l'urgent et à concentrer ton énergie sur ce qui compte vraiment.",
                'color'       => '#B8913A',
                'tip'         => "Commence chaque journée par identifier ta tâche n°1 — celle qui, si elle est faite, rendra la journée réussie.",
            ],
            'planification' => [
                'label'       => 'Planification',
                'description' => "Ta capacité à anticiper, structurer et estimer : découper, planifier, prévoir le temps réel des tâches.",
                'color'       => '#1B2B3A',
                'tip'         => "Bloque 5 minutes en fin de journée pour préparer la suivante : 3 tâches concrètes, pas plus.",
            ],
            'focus' => [
                'label'       => 'Focus & interruptions',
                'description' => "Ta capacité à rester concentré(e), résister à la dispersion et ne pas remettre au lendemain.",
                'color'       => '#3A6B5C',
                'tip'         => "Travaille par blocs de 25 min sans notifications (Pomodoro) : un seul sujet à la fois.",
            ],
            'equilibre' => [
                'label'       => 'Équilibre & énergie',
                'description' => "Ta capacité à tenir la distance : pauses, charge soutenable, alignement des tâches sur ton énergie.",
                'color'       => '#7A4B6B',
                'tip'         => "Repère ton pic d'énergie de la journée et réserve-le à ta tâche la plus exigeante.",
            ],
        ];
    }
}
