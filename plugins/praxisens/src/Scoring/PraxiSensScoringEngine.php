<?php

namespace Praxis\Plugins\PraxiSens\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PraxiSens\Data\Questions;

/**
 * Moteur de scoring du test d'hypersensibilité (Sensory Processing Sensitivity).
 *
 * Échelle 1-5 par item. Score par dimension = moyenne des items, normalisée 0-100.
 * Score global = moyenne des 3 dimensions (propriété exacte car 6 items/dimension).
 * Restitution en 4 paliers : faible <40, modérée 40-59, élevée 60-77, haute ≥78.
 */
class PraxiSensScoringEngine implements ScoringEngineContract
{
    private const SCALE_MAX = 5;

    public function key(): string
    {
        return 'praxisens-sps';
    }

    public function score(TestAttempt $attempt): array
    {
        $dims   = Questions::dimensions();
        $sums   = array_fill_keys(array_keys($dims), 0.0);
        $counts = array_fill_keys(array_keys($dims), 0.0);

        // ── 1. Agréger les réponses par dimension ──
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring   = $answer->question->scoring ?? [];
            $dimension = $scoring['dimension'] ?? null;
            $reversed  = (bool) ($scoring['reversed'] ?? false);
            $weight    = (float) ($scoring['weight'] ?? 1);

            if (!$dimension || !isset($sums[$dimension])) {
                continue;
            }

            $val = max(1, min(self::SCALE_MAX, (int) $answer->value));
            if ($reversed) {
                $val = (self::SCALE_MAX + 1) - $val;
            }

            $sums[$dimension]   += $val * $weight;
            $counts[$dimension] += $weight;
        }

        // ── 2. Scores bruts (moyenne 1-5) et normalisés (0-100) ──
        $rawScores  = [];
        $normalized = [];
        foreach ($dims as $dimKey => $_) {
            $n   = $counts[$dimKey];
            $avg = $n > 0 ? $sums[$dimKey] / $n : 1.0;
            $rawScores[$dimKey]  = round($avg, 2);
            $normalized[$dimKey] = (int) round(($avg - 1) / (self::SCALE_MAX - 1) * 100);
        }

        // ── 3. Étalonnage (percentile + label) si normes disponibles ──
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = NormInterpreter::enrich($this->key(), $dimKey, $raw);
        }

        // ── 4. Score global + palier ──
        $globalScore = (int) round(array_sum($normalized) / max(1, count($normalized)));
        [$label, $text] = $this->interpretGlobal($globalScore);

        return [
            'engine'        => $this->key(),
            'dimensions'    => $normalized,          // { eoe: 72, aes: 58, lst: 64 }
            'raw_scores'    => $rawScores,           // moyennes 1-5
            'norm_scores'   => $normScores,          // étalonnage
            'global_score'  => $globalScore,         // 0-100
            'global_label'  => $label,               // ex: "Sensibilité élevée"
            'global_text'   => $text,                // phrase de restitution
            'meta'          => $dims,                // libellés + couleurs
            'disclaimer'    => "Ce test d'hypersensibilité est un outil d'auto-réflexion inspiré du modèle "
                . "de la sensibilité de traitement sensoriel (E. Aron). Il ne constitue pas un diagnostic médical "
                . "ni psychologique.",
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    /**
     * Paliers sur le score global (0-100). Le point neutre (50) tombe en « modérée ».
     */
    protected function interpretGlobal(int $pct): array
    {
        if ($pct >= 78) {
            return ['Haute sensibilité marquée',
                "Votre profil correspond à une haute sensibilité marquée. Vous traitez l'information en profondeur et percevez finement votre environnement — une richesse qui demande aussi de protéger vos temps de récupération."];
        }
        if ($pct >= 60) {
            return ['Sensibilité élevée',
                "Une sensibilité élevée ressort de vos réponses. Vous êtes nettement réceptif(ve) aux ambiances et aux subtilités, tout en gardant des moments où les stimulations ne vous débordent pas."];
        }
        if ($pct >= 40) {
            return ['Sensibilité modérée',
                "Votre profil indique une sensibilité modérée, équilibrée. Vous percevez les nuances de votre environnement sans en être facilement submergé(e) : ni filtre systématique, ni saturation fréquente."];
        }
        return ['Sensibilité faible',
            "Votre profil indique une sensibilité plutôt faible. Vous filtrez naturellement les stimulations et restez à l'aise dans des environnements intenses."];
    }
}
