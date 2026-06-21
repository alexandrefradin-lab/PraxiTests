<?php

namespace Praxis\Plugins\PraxiFocus\Data;

/**
 * Données statiques du test PraxiFocus — repérage TDAH adulte.
 *
 * Source : Adult ADHD Self-Report Scale (ASRS-v1.1), Symptom Checklist,
 * Organisation Mondiale de la Santé (Kessler et al., 2005). Échelle libre
 * d'usage, domaine public. 18 items, fréquence sur 5 niveaux (0 à 4).
 *
 * ── Convention de scoring (champ 'scoring') ──────────────────────────────
 *   dimension          : 'inattention' | 'hyperactivite_impulsivite'
 *   part               : 'A' (screener validé, items 1-6) | 'B' (items 7-18)
 *   screener_threshold : seuil de la « case grisée » ASRS pour la Partie A
 *                        (items 1,2,3 → 2 = Parfois+ ; items 4,5,6 → 3 = Souvent+)
 *
 * ⚠️ AVERTISSEMENT : outil de REPÉRAGE, pas de diagnostic. Un score élevé
 * n'établit pas un TDAH. Seul un professionnel de santé peut diagnostiquer.
 */
class Questions
{
    /**
     * Options de fréquence ASRS (0-4). Type 'single' → la value est émise
     * directement par le frontend (0 est donc soumissible, contrairement à
     * 'scale' qui n'émet que 1..max). Voir contrat d'échelle PraxiQuest.
     */
    private static function freq(): array
    {
        return [
            ['value' => 0, 'label' => 'Jamais'],
            ['value' => 1, 'label' => 'Rarement'],
            ['value' => 2, 'label' => 'Parfois'],
            ['value' => 3, 'label' => 'Souvent'],
            ['value' => 4, 'label' => 'Très souvent'],
        ];
    }

    public static function all(): array
    {
        $f = self::freq();

        $S1 = 'Organisation & gestion du quotidien';
        $S2 = 'Concentration & agitation';
        $S3 = 'Énergie & relations';

        return [
            // ── Partie A — screener validé (items 1 à 6) ──────────────────
            [
                'section' => $S1,
                'prompt'  => "À quelle fréquence avez-vous des difficultés à finaliser les derniers détails d'un projet, une fois que les parties les plus stimulantes ont été faites ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'A', 'screener_threshold' => 2],
            ],
            [
                'section' => $S1,
                'prompt'  => "À quelle fréquence avez-vous des difficultés à mettre les choses en ordre lorsque vous devez accomplir une tâche qui demande de l'organisation ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'A', 'screener_threshold' => 2],
            ],
            [
                'section' => $S1,
                'prompt'  => "À quelle fréquence avez-vous des difficultés à vous souvenir de vos rendez-vous ou de vos obligations ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'A', 'screener_threshold' => 2],
            ],
            [
                'section' => $S1,
                'prompt'  => "Lorsque vous avez une tâche qui demande beaucoup de réflexion, à quelle fréquence évitez-vous de la commencer ou la remettez-vous à plus tard ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'A', 'screener_threshold' => 3],
            ],
            [
                'section' => $S1,
                'prompt'  => "À quelle fréquence remuez-vous ou gigotez-vous des mains ou des pieds lorsque vous devez rester assis(e) longtemps ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'A', 'screener_threshold' => 3],
            ],
            [
                'section' => $S1,
                'prompt'  => "À quelle fréquence vous sentez-vous excessivement actif(ve) et obligé(e) de faire des choses, comme si vous étiez « monté(e) sur un moteur » ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'A', 'screener_threshold' => 3],
            ],

            // ── Partie B — items complémentaires (7 à 18) ─────────────────
            [
                'section' => $S2,
                'prompt'  => "À quelle fréquence faites-vous des erreurs d'inattention lorsque vous travaillez sur un projet ennuyeux ou difficile ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'B'],
            ],
            [
                'section' => $S2,
                'prompt'  => "À quelle fréquence avez-vous des difficultés à maintenir votre attention lorsque vous faites un travail ennuyeux ou répétitif ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'B'],
            ],
            [
                'section' => $S2,
                'prompt'  => "À quelle fréquence avez-vous des difficultés à vous concentrer sur ce que les gens vous disent, même lorsqu'ils s'adressent directement à vous ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'B'],
            ],
            [
                'section' => $S2,
                'prompt'  => "À quelle fréquence égarez-vous ou avez-vous du mal à retrouver des objets à la maison ou au travail ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'B'],
            ],
            [
                'section' => $S2,
                'prompt'  => "À quelle fréquence êtes-vous distrait(e) par l'activité ou le bruit autour de vous ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'inattention', 'part' => 'B'],
            ],
            [
                'section' => $S2,
                'prompt'  => "À quelle fréquence quittez-vous votre place lors de réunions ou dans d'autres situations où vous êtes censé(e) rester assis(e) ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'B'],
            ],
            [
                'section' => $S3,
                'prompt'  => "À quelle fréquence vous sentez-vous agité(e) ou nerveux(se) ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'B'],
            ],
            [
                'section' => $S3,
                'prompt'  => "À quelle fréquence avez-vous des difficultés à vous détendre et à vous reposer lorsque vous avez du temps libre ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'B'],
            ],
            [
                'section' => $S3,
                'prompt'  => "À quelle fréquence vous surprenez-vous à parler trop dans des situations sociales ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'B'],
            ],
            [
                'section' => $S3,
                'prompt'  => "Lors d'une conversation, à quelle fréquence finissez-vous les phrases des personnes à qui vous parlez avant qu'elles ne les terminent elles-mêmes ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'B'],
            ],
            [
                'section' => $S3,
                'prompt'  => "À quelle fréquence avez-vous des difficultés à attendre votre tour dans les situations qui l'exigent ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'B'],
            ],
            [
                'section' => $S3,
                'prompt'  => "À quelle fréquence interrompez-vous les autres lorsqu'ils sont occupés ?",
                'type'    => 'single',
                'options' => $f,
                'scoring' => ['dimension' => 'hyperactivite_impulsivite', 'part' => 'B'],
            ],
        ];
    }

    /**
     * Métadonnées des dimensions — affichage + prompts IA.
     */
    public static function dimensions(): array
    {
        return [
            'inattention' => [
                'label'       => 'Inattention',
                'description' => "Difficultés de concentration, d'organisation, de mémoire de travail et de suivi des tâches.",
                'color'       => '#A67520', // var(--pt-gold)
            ],
            'hyperactivite_impulsivite' => [
                'label'       => 'Hyperactivité / Impulsivité',
                'description' => "Agitation intérieure ou physique, besoin de bouger, difficulté à attendre, tendance à interrompre.",
                'color'       => '#1B2B3A', // var(--pt-navy)
            ],
        ];
    }
}
