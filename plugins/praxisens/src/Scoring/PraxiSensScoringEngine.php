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
 * Score global = moyenne des dimensions (6 items/dimension, calcul dynamique).
 * Dimensions : eoe (sur-stimulation), aes (esthétique), lst (seuil sensoriel), emo (émotionnel).
 * Restitution en 4 paliers : faible <40, modérée 40-59, élevée 60-77, haute ≥78.
 *
 * Échelle de validité : 6 items de contrôle (dimension 'ctrl', style Marlowe-Crowne),
 * exclus du score sensoriel, produisant un score de désirabilité sociale et un flag
 * de fiabilité — même mécanique que praxiemo (EqiScoringEngine::desirabilite).
 */
class PraxiSensScoringEngine implements ScoringEngineContract
{
    private const SCALE_MAX = 5;

    /** Dimension des items de contrôle (désirabilité sociale). */
    private const CTRL_DIMENSION = 'ctrl';

    /** Nombre d'items de contrôle attendus. */
    private const CTRL_COUNT = 6;

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
        $ctrlSum   = 0;
        $ctrlCount = 0;
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $scoring   = $answer->question->scoring ?? [];
            $dimension = $scoring['dimension'] ?? null;
            $reversed  = (bool) ($scoring['reversed'] ?? false);
            $weight    = (float) ($scoring['weight'] ?? 1);

            // Items de contrôle : collectés à part, jamais dans le score sensoriel.
            if ($dimension === self::CTRL_DIMENSION) {
                $ctrlSum += max(1, min(self::SCALE_MAX, (int) $answer->value));
                $ctrlCount++;
                continue;
            }

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

        // ── 2. Désirabilité sociale + correction douce (mécanique praxiemo) ──
        // En cas de biais de présentation, on régresse chaque moyenne vers le
        // milieu d'échelle (3 sur 1-5) : un profil « trop parfait » minimise
        // souvent sa sensibilité, la correction ramène vers une zone plus plausible.
        $ds     = $this->desirabilite($ctrlSum, $ctrlCount);
        $shrink = match ($ds['niveau']) {
            'Biais fort'   => 0.80,
            'Biais modéré' => 0.90,
            default        => 1.0,
        };

        // ── 3. Scores bruts (moyenne 1-5) et normalisés (0-100) ──
        $rawScores  = [];
        $normalized = [];
        foreach ($dims as $dimKey => $_) {
            $n   = $counts[$dimKey];
            $avg = $n > 0 ? $sums[$dimKey] / $n : 1.0;
            if ($shrink < 1.0) {
                $avg = 3.0 + ($avg - 3.0) * $shrink;
            }
            $rawScores[$dimKey]  = round($avg, 2);
            $normalized[$dimKey] = (int) round(($avg - 1) / (self::SCALE_MAX - 1) * 100);
        }

        // ── 4. Étalonnage (percentile + label) si normes disponibles ──
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = NormInterpreter::enrich($this->key(), $dimKey, $raw);
        }

        // ── 5. Score global + palier ──
        $globalScore = (int) round(array_sum($normalized) / max(1, count($normalized)));
        [$label, $text] = $this->interpretGlobal($globalScore);

        // dimension_meta au niveau racine : ResultsShow.vue peut lire
        // result.scoring.dimension_meta[key].{label,description} comme filet de sécurité.
        $dimensionMeta = [];
        foreach ($dims as $key => $d) {
            $dimensionMeta[$key] = ['label' => $d['label'], 'description' => $d['description']];
        }

        return [
            'engine'         => $this->key(),
            'dimensions'     => $normalized,          // { eoe: 72, aes: 58, lst: 64 }
            'raw_scores'     => $rawScores,           // moyennes 1-5
            'norm_scores'    => $normScores,          // étalonnage
            'global_score'   => $globalScore,         // 0-100
            'global_label'   => $label,               // ex: "Sensibilité élevée"
            'global_text'    => $text,                // phrase de restitution
            'meta'           => $dims,                // libellés + couleurs (page personnalisée)
            'dimension_meta' => $dimensionMeta,       // filet de sécurité pour ResultsShow
            'desirabilite'   => $ds,                  // { score, niveau, alerte, message }
            'disclaimer'    => "Ce test d'hypersensibilité est un outil d'auto-réflexion inspiré du modèle "
                . "de la sensibilité de traitement sensoriel (E. Aron). Il ne constitue pas un diagnostic médical "
                . "ni psychologique.",
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    /**
     * Score de désirabilité sociale sur les items de contrôle (dimension 'ctrl').
     *
     * Items style Marlowe-Crowne : ils décrivent des comportements humains normaux
     * que presque tout le monde reconnaît avoir PARFOIS. Répondre « Pas du tout
     * d'accord » (1) à ces items = présentation trop parfaite = biais probable.
     * C'est donc un score BAS qui signale un biais, pas un score élevé.
     * Plage totale : 6 (min) à 30 (max) — 6 items, échelle 1-5.
     * Seuils alignés sur praxiemo (moyenne ≤ 2 → fort, ≤ 3 → modéré) :
     *   <= 12 → Biais fort   (réponses trop parfaites, image très positive de soi)
     *   <= 18 → Biais modéré (légère tendance à la présentation avantageuse)
     *   >  18 → Fiable       (admission de faiblesses humaines normales)
     */
    protected function desirabilite(int $sum, int $count): array
    {
        // Anciennes passations (avant l'ajout de l'échelle de validité) :
        // aucun item de contrôle en base, on ne mesure rien plutôt que d'inventer.
        if ($count === 0) {
            return ['score' => null, 'niveau' => 'Non mesuré', 'alerte' => false, 'message' => ''];
        }

        // Item de contrôle manquant = 1 (convention praxiemo : l'absence de réponse
        // ne doit jamais rendre le profil plus fiable qu'il ne l'est).
        $sum += max(0, self::CTRL_COUNT - $count);

        if ($sum <= 12) {
            return [
                'score'   => $sum,
                'niveau'  => 'Biais fort',
                'alerte'  => true,
                'message' => "Vos réponses semblent orientées vers une image très positive de vous-même. Votre profil de sensibilité reflète peut-être davantage ce que vous souhaiteriez montrer que ce que vous vivez au quotidien. Un regard plus nuancé sur vos ressentis pourrait affiner ces résultats.",
            ];
        }
        if ($sum <= 18) {
            return ['score' => $sum, 'niveau' => 'Biais modéré', 'alerte' => false, 'message' => ''];
        }
        return ['score' => $sum, 'niveau' => 'Fiable', 'alerte' => false, 'message' => ''];
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
