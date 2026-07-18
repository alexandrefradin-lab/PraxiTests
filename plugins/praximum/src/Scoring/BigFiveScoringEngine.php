<?php

namespace Praxis\Plugins\PraxiMum\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\Scoring\SocialDesirability;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
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
            $norm = $normes[$fk] ?? ['mean' => 10.0, 'std_dev' => 2.5];
            $T = $this->computeT($brut, $norm['mean'], $norm['std_dev']);
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
            ? (int) round((($dsBrut - $dsCount) / (3 * $dsCount)) * 100)
            : 0;

        // Correction douce de désirabilité : un biais de présentation positive
        // gonfle les scores. On régresse alors chaque dimension vers la moyenne
        // (T=50) proportionnellement au biais détecté. Échelle en % (score HAUT
        // = biais), seuils historiques 60/75 — mécanique partagée avec les
        // échelles de contrôle Marlowe-Crowne de praxiemo/praxisens via le
        // service Praxis\Core\Scoring\SocialDesirability.
        $dsNiveau = SocialDesirability::levelFromBiasPercent($dsPct);
        $shrink   = SocialDesirability::shrinkFactor($dsNiveau);
        if ($shrink < 1.0) {
            foreach ($scoresDim as $dim => $data) {
                $T = max(20, min(80, (int) round(SocialDesirability::shrink($data['T'], 50, $shrink))));
                $scoresDim[$dim] = [
                    'T'      => $T,
                    'pct'    => $this->tToPct($T),
                    'niveau' => $this->niveauT($T),
                    'label'  => $data['label'],
                ];
            }
        }

        // Stabilisation de l'archétype : si aucune dimension ne se détache
        // nettement (amplitude T < 8), on évite de plaquer un profil en 5 lettres
        // sur une frontière fragile à T=50 → on renvoie le Profil Équilibré.
        $tValues  = array_map(fn ($d) => $d['T'], $scoresDim);
        $tSpread  = $tValues ? max($tValues) - min($tValues) : 0;
        $archetype = ($tSpread >= 8 ? ArchetypeResolver::resolve($scoresDim) : null) ?? [
            'key'         => 'BALANCED',
            'nom'         => 'Le Profil Équilibré',
            'tagline'     => 'Un profil multidimensionnel, adaptable selon les contextes.',
            'emoji'       => '🌓',
            'description' => "Vos dimensions s'équilibrent sans qu'aucune ne domine nettement. Cette polyvalence vous permet de vous adapter à des contextes variés.",
            'rarete'      => 50,
            'couleur1'    => '#6366f1',
            'couleur2'    => '#1E2A3A',
            'traits'      => ['Adaptabilité', 'Équilibre', 'Polyvalence'],
        ];

        // Étalonnage BigFive — percentile calculé directement depuis le T-score
        // (T-score = already normed: mean=50, sd=10 par définition)
        $normScores = [];
        foreach ($scoresDim as $dim => $data) {
            $normScores[$dim] = NormInterpreter::fromTScore($data['T']);
        }

        return [
            'engine'           => $this->key(),
            'scores_dim'       => $scoresDim,
            'norm_scores'      => $normScores,
            'scores_facette'   => $scoresFacette,
            'archetype'        => $archetype,
            'desirabilite'     => [
                'brut'   => $dsBrut,
                'pct'    => $dsPct,
                'niveau' => $dsNiveau,
                'alert'  => $dsNiveau === SocialDesirability::FORT,
            ],
            'meta_dimensions'  => Catalog::dimensions(),
            'meta_facettes'    => Catalog::facettes(),
            'computed_at'      => now()->toIso8601String(),
        ];
    }

    protected function computeT(int|float $brut, float $mean, float $sd): int
    {
        if ($sd <= 0) return 50;
        // T-score standard (moyenne 50, écart-type 10). Pas d'amplification
        // artificielle : l'ancien facteur ×1,15 surévaluait mécaniquement de
        // ~3-4 points de percentile en zone médiane-haute (audit 2026-06-21).
        $T = 50 + 10 * (($brut - $mean) / $sd);
        return (int) round(max(20, min(80, $T)));
    }

    protected function tToPct(int $T): int
    {
        $clamped = max(20, min(80, $T));
        // Utiliser la vraie CDF normale de NormInterpreter (cohérent avec les autres engines)
        $norm = NormInterpreter::fromTScore($clamped);
        // Utiliser la vraie CDF normale (cohérent avec les autres moteurs) — QC-24
        return (int) round($norm['percentile'] ?? (($clamped - 20) * 100 / 60));
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
