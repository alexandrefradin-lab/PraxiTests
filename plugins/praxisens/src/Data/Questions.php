<?php

namespace Praxis\Plugins\PraxiSens\Data;

/**
 * Données statiques du test d'hypersensibilité (PraxiSens).
 *
 * Échelle de Likert 1→5 (le frontend émet 1..max, jamais 0 — contrat d'échelle).
 * Tous les items sont cotés positivement (5 = plus sensible) : aucune inversion.
 *
 * Sous-dimensions (Smolewska et al., 2006) :
 *   eoe = Facilité de saturation / sur-stimulation
 *   aes = Sensibilité esthétique & profondeur de traitement
 *   lst = Seuil sensoriel bas
 */
class Questions
{
    /** Libellés communs de l'échelle de Likert 5 points. */
    private static function scale(string $section, string $prompt, string $dimension): array
    {
        return [
            'section' => $section,
            'prompt'  => $prompt,
            'type'    => 'scale',
            'options' => [
                'min'       => 1,
                'max'       => 5,
                'min_label' => "Pas du tout d'accord",
                'max_label' => "Tout à fait d'accord",
                'labels'    => [
                    "Pas du tout d'accord",
                    "Plutôt pas d'accord",
                    "Neutre",
                    "Plutôt d'accord",
                    "Tout à fait d'accord",
                ],
            ],
            'scoring' => ['dimension' => $dimension, 'weight' => 1],
        ];
    }

    public static function all(): array
    {
        $eoe = 'Sur-stimulation';
        $aes = 'Sensibilité esthétique';
        $lst = 'Seuil sensoriel';
        $emo = 'Sensibilité émotionnelle';

        return [
            // ── EOE — Facilité de saturation / sur-stimulation ──
            self::scale($eoe, "Les humeurs des personnes autour de moi déteignent fortement sur la mienne.", 'eoe'),
            self::scale($eoe, "Quand j'ai beaucoup à faire en peu de temps, je me sens vite débordé(e).", 'eoe'),
            self::scale($eoe, "Après une journée chargée, j'ai besoin de me retirer au calme pour récupérer.", 'eoe'),
            self::scale($eoe, "Mon système nerveux se sent parfois tellement saturé que je dois m'isoler.", 'eoe'),
            self::scale($eoe, "Je sursaute facilement.", 'eoe'),
            self::scale($eoe, "Cela me gêne quand on me demande de faire trop de choses à la fois.", 'eoe'),

            // ── AES — Sensibilité esthétique & profondeur ──
            self::scale($aes, "Je perçois dans mon environnement des subtilités que beaucoup de gens ne remarquent pas.", 'aes'),
            self::scale($aes, "J'ai une vie intérieure riche et complexe.", 'aes'),
            self::scale($aes, "Je suis profondément ému(e) par les arts ou la musique.", 'aes'),
            self::scale($aes, "Je remarque et savoure les parfums, les saveurs ou les sons délicats.", 'aes'),
            self::scale($aes, "Quand quelqu'un est mal à l'aise dans un lieu, je sens souvent ce qu'il faudrait changer.", 'aes'),
            self::scale($aes, "Je réfléchis longuement et en profondeur aux choses qui me touchent.", 'aes'),

            // ── LST — Seuil sensoriel bas ──
            self::scale($lst, "Je suis facilement submergé(e) par des stimulations fortes (lumières vives, odeurs puissantes, bruits).", 'lst'),
            self::scale($lst, "Les bruits forts me mettent mal à l'aise.", 'lst'),
            self::scale($lst, "Je suis sensible à la douleur.", 'lst'),
            self::scale($lst, "Je suis particulièrement sensible aux effets de la caféine.", 'lst'),
            self::scale($lst, "Les textures rugueuses, les étiquettes ou certains tissus sur la peau me dérangent.", 'lst'),
            self::scale($lst, "Une faim intense provoque chez moi une forte réaction (humeur ou concentration perturbées).", 'lst'),

            // ── EMO — Sensibilité émotionnelle ──
            self::scale($emo, "Même un conflit ou une tension légère dans mon entourage me trouble profondément, même si je n'en suis pas l'objet.", 'emo'),
            self::scale($emo, "Je ressens les émotions négatives (tristesse, honte, peur) avec une intensité qui peut m'envahir entièrement.", 'emo'),
            self::scale($emo, "La désapprobation ou le rejet, même perçu(e), m'affecte de façon durable et difficile à mettre de côté.", 'emo'),
            self::scale($emo, "Je pleure ou suis submergé(e) par l'émotion plus facilement que la plupart des gens autour de moi.", 'emo'),
            self::scale($emo, "Assister à une scène triste (film, actualité, récit) me laisse avec une émotion forte et persistante bien après.", 'emo'),
            self::scale($emo, "Mon entourage m'a déjà dit que je réagissais de façon excessive ou que je prenais les choses trop à cœur.", 'emo'),
        ];
    }

    /**
     * Métadonnées des dimensions — affichage et prompts IA.
     */
    public static function dimensions(): array
    {
        return [
            'eoe' => [
                'label'       => 'Sur-stimulation',
                'description' => "Tendance à être vite débordé(e) par les sollicitations internes ou externes, et besoin de se retirer pour récupérer.",
                'color'       => '#7c3aed',
            ],
            'aes' => [
                'label'       => 'Sensibilité esthétique',
                'description' => "Perception fine des subtilités, richesse de la vie intérieure et émotion profonde face au beau.",
                'color'       => '#0ea5e9',
            ],
            'lst' => [
                'label'       => 'Seuil sensoriel',
                'description' => "Réactivité intense aux stimulations sensorielles : bruit, lumière, textures, douleur, substances.",
                'color'       => '#10b981',
            ],
            'emo' => [
                'label'       => 'Sensibilité émotionnelle',
                'description' => "Intensité des émotions ressenties, contagion émotionnelle, vulnérabilité au rejet et à la désapprobation, empathie profonde.",
                'color'       => '#e11d48', // rose vif
            ],
        ];
    }
}
