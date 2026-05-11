<?php

namespace Praxis\Plugins\PraxiMum\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Plugins\PraxiMum\Archetypes\ArchetypeResolver;
use Praxis\Plugins\PraxiMum\Data\Catalog;

class BigFiveScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praximum-bigfive';
    }

    public function score(TestAttempt $attempt): array
    {
        $questions = collect(Catalog::questions())->keyBy('id');
        $byId = [];
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $qid = (int) ($answer->question->scoring['qid'] ?? 0);
            if ($qid > 0) $byId[$qid] = max(1, min(4, (int) $answer->value));
        }

        // Accumulateurs
        $facBrut = []; $facCount = [];
        foreach (array_keys(Catalog::facettes()) as $fk) {
            $facBrut[$fk] = 0; $facCount[$fk] = 0;
        }
        $dsBrut = 0; $dsCount = 0;

        foreach ($byId as $qid => $val) {
            $q = $questions[$qid] ?? null;
            if (!$q) continue;
            $score = $q['inv'] ? (5 - $val) : $val;
            if ($q['dim'] === 'DS') {
                $dsBrut += $score; $dsCount++;
                continue;
            }
            $fac = $q['facette'] ?? null;
            if (!$fac) continue;
            $facBrut[$fac] += $score; $facCount[$fac]++;
        }

        // ── Scores facettes (T) ────────────────────────────────
        $normes = Catalog::normes();
        $scoresFacette = [];
        foreach ($facBrut as $fk => $brut) {
            $n = $facCount[$fk];
            if ($n === 0) {
                $scoresFacette[$fk] = ['brut' => 0, 'T' => 50, 'pct' => 50, 'niveau' => 'moyen'];
                continue;
            }
            $norm = $normes[$fk] ?? ['mean' => 10.0, 'sd' => 2.5];
            $T = $this->computeT($brut, $norm['mean'], $norm['sd']);
            $scoresFacette[$fk] = [
                'brut'   => $brut,
                'T'      => $T,
                'pct'    => $this->tToPct($T),
                'niveau' => $this->niveauT($T),
            ];
        }

        // ── Scores dimensions (moyenne T des facettes) ─────────
        $facettesByDim = [];
        foreach (Catalog::facettes() as $fk => $info) {
            $facettesByDim[$info['dim']][] = $fk;
        }

        $scoresDim = [];
        foreach (Catalog::dimensions() as $dim => $meta) {
            $tVals = [];
            foreach ($facettesByDim[$dim] ?? [] as $fk) {
                $tVals[] = $scoresFacette[$fk]['T'];
            }
            $T = $tVals ? (int) round(array_sum($tVals) / count($tVals)) : 50;
            $T = max(20, min(80, $T));
            $scoresDim[$dim] = [
                'T'      => $T,
                'pct'    => $this->tToPct($T),
                'niveau' => $this->niveauT($T),
                'label'  => $meta['label'],
            ];
        }

        // ── Désirabilité Sociale (% simple) ───────────────────
        $dsPct = $dsCount > 0
            ? (int) round((($dsBrut - $dsCount) / (4 * $dsCount)) * 100)
            : 0;

        $archetype = ArchetypeResolver::resolve($scoresDim);

        return [
            'engine'           => $this->key(),
            'scores_dim'       => $scoresDim,
            'scores_facette'   => $scoresFacette,
            'archetype'        => $archetype,
            'desirabilite'     => [
                'brut'  => $dsBrut,
                'pct'   => $dsPct,
                'alert' => $dsPct >= 75,
            ],
            'meta_dimensions'  => Catalog::dimensions(),
            'meta_facettes'    => Catalog::facettes(),
            'computed_at'      => now()->toIso8601String(),
        ];
    }

    protected function computeT(int $brut, float $mean, float $sd): int
    {
        if ($sd <= 0) return 50;
        $z = ($brut - $mean) / $sd;
        $T = (int) round(50 + 10 * $z);
        return max(20, min(80, $T));
    }

    protected function tToPct(int $T): int
    {
        // T (20-80) → percentile approximatif (cumulative normal).
        // Simple: clamp linéaire 20→2, 50→50, 80→98.
        $clamped = max(20, min(80, $T));
        return (int) round(($clamped - 20) * 100 / 60);
    }

    protected function niveauT(int $T): string
    {
        if ($T < 35)  return 'tres_bas';
        if ($T < 45)  return 'bas';
        if ($T <= 55) return 'moyen';
        if ($T <= 65) return 'haut';
        return 'tres_haut';
    }
}
