<?php

namespace Praxis\Plugins\Praxis360\Support;

use App\Models\EvaluationPanel;
use App\Models\TestAttempt;
use Praxis\Plugins\Praxis360\Data\Questions;
use Praxis\Plugins\Praxis360\Scoring\Praxis360ScoringEngine;

/**
 * Agrège un panel 360° : compare l'auto-évaluation du candidat aux regards des
 * évaluateurs (manager / pairs / collaborateurs), calcule les écarts et les
 * angles morts, et applique le seuil d'anonymat (un groupe n'est affiché que
 * s'il compte au moins `anonymity_threshold` réponses ; sinon ses données sont
 * fondues dans l'agrégat global « tous évaluateurs »).
 */
class PanelAggregator
{
    public function __construct(private EvaluationPanel $panel)
    {
    }

    public function build(): array
    {
        $threshold = max(1, (int) $this->panel->anonymity_threshold);
        $dims      = Questions::dimensions();
        $dimKeys   = array_keys($dims);

        // Tentatives complètes des évaluateurs, groupées par relation.
        $raterAttempts = $this->panel->raterAttempts()
            ->where('status', 'completed')
            ->with(['answers.question'])
            ->get();

        $byRelation = [];
        foreach (EvaluationPanel::RELATIONS as $rel) {
            $byRelation[$rel] = $raterAttempts->where('rater_relation', $rel);
        }

        $totalRaters = $raterAttempts->count();
        $invited     = $this->panel->invitations()->count();

        // Auto-évaluation (référence).
        $self = $this->panel->selfAttempt
            ? $this->dimensionScores($this->panel->selfAttempt, $dimKeys)
            : [];

        // Pas assez de répondants au total → on n'expose rien de nominatif.
        if ($totalRaters < $threshold) {
            return [
                'available' => false,
                'threshold' => $threshold,
                'counts'    => $this->counts($byRelation, $totalRaters, $invited),
                'self'      => $self,
                'meta'      => $dims,
                'message'   => "Les regards des évaluateurs s'afficheront dès que {$threshold} réponses au moins auront été recueillies (anonymat garanti).",
            ];
        }

        // Moyenne « tous évaluateurs » par dimension.
        $others = $this->meanScores($raterAttempts->all(), $dimKeys);

        // Groupes affichables individuellement (≥ seuil).
        $groups = [];
        foreach (EvaluationPanel::RELATIONS as $rel) {
            $grp = $byRelation[$rel];
            if ($grp->count() >= $threshold) {
                $groups[] = [
                    'relation' => $rel,
                    'label'    => \App\Models\EvaluationInvitation::RELATION_LABELS[$rel] ?? $rel,
                    'count'    => $grp->count(),
                    'scores'   => $this->meanScores($grp->all(), $dimKeys),
                ];
            }
        }

        // Écarts (autres − soi) et angles morts.
        $gaps       = [];
        $blindSpots = [];
        foreach ($dimKeys as $k) {
            if (!isset($self[$k]) || !isset($others[$k])) {
                continue;
            }
            $gap = $others[$k] - $self[$k];
            $gaps[$k] = $gap;

            $type = null;
            if ($self[$k] >= 60 && $gap <= -15) {
                $type = 'angle_mort';      // se voit mieux que les autres ne le perçoivent
            } elseif ($self[$k] <= 55 && $gap >= 15) {
                $type = 'force_cachee';    // les autres le perçoivent mieux qu'il ne se voit
            }
            if ($type) {
                $blindSpots[] = [
                    'dimension' => $k,
                    'label'     => $dims[$k]['label'] ?? $k,
                    'self'      => $self[$k],
                    'others'    => $others[$k],
                    'gap'       => $gap,
                    'type'      => $type,
                ];
            }
        }
        usort($blindSpots, fn ($a, $b) => abs($b['gap']) <=> abs($a['gap']));

        return [
            'available'   => true,
            'threshold'   => $threshold,
            'counts'      => $this->counts($byRelation, $totalRaters, $invited),
            'self'        => $self,
            'others'      => $others,
            'groups'      => $groups,
            'gaps'        => $gaps,
            'blind_spots' => $blindSpots,
            'verbatims'   => $this->collectVerbatims(),
            'meta'        => $dims,
        ];
    }

    /** Scores 0-100 par dimension pour une tentative (moyenne des items 1-5). */
    private function dimensionScores(TestAttempt $attempt, array $dimKeys): array
    {
        $sums   = array_fill_keys($dimKeys, 0.0);
        $counts = array_fill_keys($dimKeys, 0);

        foreach ($attempt->answers as $answer) {
            $dim = $answer->question->scoring['dimension'] ?? null;
            if ($dim === null || !isset($sums[$dim])) {
                continue;
            }
            if ($answer->value === null || $answer->value === '') {
                continue;
            }
            $sums[$dim]   += max(1, min(5, (int) $answer->value));
            $counts[$dim] += 1;
        }

        $out = [];
        foreach ($dimKeys as $k) {
            if ($counts[$k] > 0) {
                $avg = $sums[$k] / $counts[$k];
                $out[$k] = Praxis360ScoringEngine::normalizeAvg($avg);
            }
        }
        return $out;
    }

    /** Moyenne, par dimension, des scores d'un ensemble de tentatives. */
    private function meanScores(array $attempts, array $dimKeys): array
    {
        $acc = array_fill_keys($dimKeys, []);
        foreach ($attempts as $attempt) {
            foreach ($this->dimensionScores($attempt, $dimKeys) as $k => $v) {
                $acc[$k][] = $v;
            }
        }
        $out = [];
        foreach ($dimKeys as $k) {
            if ($acc[$k]) {
                $out[$k] = (int) round(array_sum($acc[$k]) / count($acc[$k]));
            }
        }
        return $out;
    }

    private function counts(array $byRelation, int $total, int $invited): array
    {
        $c = ['total' => $total, 'invited' => $invited];
        foreach (EvaluationPanel::RELATIONS as $rel) {
            $c[$rel] = $byRelation[$rel]->count();
        }
        return $c;
    }

    /**
     * Verbatims poolés et anonymisés (sans attribution nominative ni de relation)
     * pour préserver l'anonymat. N'apparaissent que si le seuil global est atteint
     * (vérifié en amont par build()).
     */
    private function collectVerbatims(): array
    {
        $keys = array_keys(Questions::verbatims());
        $out  = array_fill_keys($keys, []);

        $completed = $this->panel->invitations()
            ->where('status', 'completed')
            ->whereNotNull('verbatims')
            ->get();

        foreach ($completed as $inv) {
            foreach ($keys as $key) {
                $text = trim((string) ($inv->verbatims[$key] ?? ''));
                if ($text !== '') {
                    $out[$key][] = $text;
                }
            }
        }
        return $out;
    }
}
