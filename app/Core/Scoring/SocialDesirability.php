<?php

namespace Praxis\Core\Scoring;

/**
 * Échelle de validité « désirabilité sociale » — implémentation unique.
 *
 * Trois moteurs (praxiemo EQ-i, praxisens SPS, praximum Big Five) embarquaient
 * chacun leur copie du même dispositif Marlowe-Crowne : items de contrôle
 * décrivant des comportements humains normaux que presque tout le monde
 * reconnaît avoir PARFOIS. Une admission basse = présentation trop parfaite
 * = biais probable. Les seuils sont exprimés en PROPORTION de l'échelle
 * (référence praxiemo : fort ≤ 1/3 de la plage d'admission, modéré ≤ 2/3),
 * pas en valeur absolue — un seuil absolu recopié entre échelles différentes
 * donnait une sévérité incohérente (praxisens ≤18/30 = 60 % vs praxiemo
 * ≤18/24 = 75 %, détecté par le knowledge graph du 2026-07-18).
 *
 * La « correction douce » régresse chaque score vers le milieu d'échelle
 * (facteur 0,80 en biais fort, 0,90 en biais modéré) : on ne survalorise pas
 * une auto-image flatteuse, sans écraser le profil pour autant.
 */
final class SocialDesirability
{
    public const FIABLE     = 'Fiable';
    public const MODERE     = 'Biais modéré';
    public const FORT       = 'Biais fort';
    public const NON_MESURE = 'Non mesuré';

    /** Biais fort : admission ≤ 1/3 de la plage. */
    private const SEUIL_FORT = 1 / 3;

    /** Biais modéré : admission ≤ 2/3 de la plage. */
    private const SEUIL_MODERE = 2 / 3;

    /**
     * Évalue une échelle de contrôle style Marlowe-Crowne à partir de la somme
     * des réponses aux items de contrôle.
     *
     * @param int    $sum         Somme des réponses effectivement données.
     * @param int    $answered    Nombre d'items de contrôle répondus.
     * @param int    $itemCount   Nombre d'items de contrôle attendus.
     * @param int    $itemMax     Valeur max d'un item (4 pour praxiemo, 5 pour praxisens).
     * @param int    $itemMin     Valeur min d'un item.
     * @param string $messageFort Message de restitution affiché en biais fort.
     *
     * @return array{score:int|null, niveau:string, alerte:bool, message:string}
     */
    public static function fromControlSum(
        int $sum,
        int $answered,
        int $itemCount,
        int $itemMax,
        int $itemMin = 1,
        string $messageFort = '',
    ): array {
        // Anciennes passations (échelle de validité absente) : on ne mesure
        // rien plutôt que d'inventer — et surtout on ne corrige pas.
        if ($answered <= 0) {
            return ['score' => null, 'niveau' => self::NON_MESURE, 'alerte' => false, 'message' => ''];
        }

        // Item de contrôle manquant = minimum : l'absence de réponse ne doit
        // jamais rendre le profil plus fiable qu'il ne l'est.
        $sum += max(0, $itemCount - $answered) * $itemMin;

        $min = $itemMin * $itemCount;
        $max = $itemMax * $itemCount;
        $admission = $max > $min ? ($sum - $min) / ($max - $min) : 1.0;

        if ($admission <= self::SEUIL_FORT) {
            return ['score' => $sum, 'niveau' => self::FORT, 'alerte' => true, 'message' => $messageFort];
        }
        if ($admission <= self::SEUIL_MODERE) {
            return ['score' => $sum, 'niveau' => self::MODERE, 'alerte' => false, 'message' => ''];
        }

        return ['score' => $sum, 'niveau' => self::FIABLE, 'alerte' => false, 'message' => ''];
    }

    /**
     * Variante « pourcentage de désirabilité » (praximum Big Five) : l'échelle
     * y est exprimée en % où un score HAUT signale le biais. Les seuils par
     * défaut (60/75) préservent le comportement historique du moteur.
     */
    public static function levelFromBiasPercent(int $pct, int $fortAt = 75, int $modereAt = 60): string
    {
        if ($pct >= $fortAt) {
            return self::FORT;
        }
        if ($pct >= $modereAt) {
            return self::MODERE;
        }

        return self::FIABLE;
    }

    /** Facteur de correction douce associé à un niveau de biais. */
    public static function shrinkFactor(string $niveau): float
    {
        return match ($niveau) {
            self::FORT   => 0.80,
            self::MODERE => 0.90,
            default      => 1.0,
        };
    }

    /** Régresse une valeur vers le milieu d'échelle selon le facteur de correction. */
    public static function shrink(float $value, float $midpoint, float $factor): float
    {
        return $midpoint + ($value - $midpoint) * $factor;
    }
}
