<?php

namespace Praxis\Plugins\PraxiMum\Data;

class Catalog
{
    /** Charge les 128 questions depuis le JSON livré avec le plugin. */
    public static function questions(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = json_decode(file_get_contents(__DIR__ . '/questions.json'), true) ?: [];
        }
        return $cache;
    }

    public static function dimensions(): array
    {
        return [
            'O' => ['label' => 'Ouverture',         'court' => "Imagination, curiosité, goût du nouveau", 'color' => '#8b5cf6'],
            'C' => ['label' => 'Conscience',        'court' => "Organisation, fiabilité, persévérance",   'color' => '#0ea5e9'],
            'E' => ['label' => 'Extraversion',      'court' => "Énergie sociale, affirmation, action",    'color' => '#f59e0b'],
            'A' => ['label' => 'Agréabilité',       'court' => "Coopération, confiance, empathie",        'color' => '#10b981'],
            'N' => ['label' => 'Neuroticisme',      'court' => "Sensibilité émotionnelle, anxiété",       'color' => '#ef4444'],
        ];
    }

    public static function facettes(): array
    {
        return [
            'O1_FAN' => ['label' => 'Fantaisie',              'dim' => 'O', 'court' => "Imagination vive, rêverie, vie intérieure riche."],
            'O2_EST' => ['label' => 'Esthétique',             'dim' => 'O', 'court' => "Sensibilité à l'art et à la beauté."],
            'O3_SEN' => ['label' => 'Sentiments',             'dim' => 'O', 'court' => "Conscience et importance accordée à ses émotions."],
            'O4_ACT' => ['label' => 'Actions',                'dim' => 'O', 'court' => "Goût de la nouveauté et de l'expérimentation."],
            'O5_IDE' => ['label' => 'Idées',                  'dim' => 'O', 'court' => "Curiosité intellectuelle, plaisir de réfléchir."],
            'O6_VAL' => ['label' => 'Valeurs',                'dim' => 'O', 'court' => "Ouverture à remettre en question les conventions."],
            'C1_COM' => ['label' => 'Compétence',             'dim' => 'C', 'court' => "Sentiment d'être capable et efficace."],
            'C2_ORD' => ['label' => 'Ordre',                  'dim' => 'C', 'court' => "Organisation, méthode, goût du rangement."],
            'C3_DEV' => ['label' => 'Sens du devoir',         'dim' => 'C', 'court' => "Respect de ses engagements et de ses principes."],
            'C4_REA' => ['label' => 'Recherche de réussite',  'dim' => 'C', 'court' => "Ambition et exigence de résultats."],
            'C5_DIS' => ['label' => 'Autodiscipline',         'dim' => 'C', 'court' => "Capacité à se motiver et à aller au bout."],
            'C6_DEL' => ['label' => 'Délibération',           'dim' => 'C', 'court' => "Réflexion avant d'agir, prudence."],
            'E1_CHA' => ['label' => 'Chaleur',                'dim' => 'E', 'court' => "Cordialité, facilité à créer des liens chaleureux."],
            'E2_GRE' => ['label' => 'Grégarité',              'dim' => 'E', 'court' => "Goût de la compagnie et des groupes."],
            'E3_ASS' => ['label' => 'Assertivité',            'dim' => 'E', 'court' => "Aisance à s'affirmer et à prendre le lead."],
            'E4_ACT' => ['label' => 'Activité',               'dim' => 'E', 'court' => "Rythme de vie soutenu, énergie, dynamisme."],
            'E5_STI' => ['label' => 'Recherche de sensations','dim' => 'E', 'court' => "Attrait pour l'excitation et la stimulation."],
            'E6_EMO' => ['label' => 'Émotions positives',     'dim' => 'E', 'court' => "Tendance à la joie et à l'enthousiasme."],
            'A1_CON' => ['label' => 'Confiance',              'dim' => 'A', 'court' => "Tendance à croire en la sincérité des autres."],
            'A2_DRO' => ['label' => 'Droiture',               'dim' => 'A', 'court' => "Franchise, sincérité, refus de manipuler."],
            'A3_ALT' => ['label' => 'Altruisme',              'dim' => 'A', 'court' => "Souci actif du bien-être d'autrui."],
            'A4_COM' => ['label' => 'Compliance',             'dim' => 'A', 'court' => "Tendance à coopérer plutôt qu'à s'opposer."],
            'A5_MOD' => ['label' => 'Modestie',               'dim' => 'A', 'court' => "Humilité et discrétion sur soi-même."],
            'A6_SEN' => ['label' => 'Sensibilité',            'dim' => 'A', 'court' => "Compassion, attention aux besoins des autres."],
            'N1_ANX' => ['label' => 'Anxiété',                'dim' => 'N', 'court' => "Tendance à l'inquiétude et à la nervosité."],
            'N2_HOS' => ['label' => 'Hostilité',              'dim' => 'N', 'court' => "Propension à la colère et à la frustration."],
            'N3_DEP' => ['label' => 'Mélancolie',             'dim' => 'N', 'court' => "Tendance au découragement et à la tristesse."],
            'N4_CON' => ['label' => 'Conscience de soi',      'dim' => 'N', 'court' => "Gêne sociale, sensibilité au jugement."],
            'N5_IMP' => ['label' => 'Impulsivité',            'dim' => 'N', 'court' => "Difficulté à résister aux envies immédiates."],
            'N6_VUL' => ['label' => 'Vulnérabilité',          'dim' => 'N', 'court' => "Sensibilité au stress sous pression."],
        ];
    }

    /** Normes population française (mean/std_dev) — issu du WP plugin v1.4.1. */
    public static function normes(): array
    {
        return [
            'O1_FAN' => ['mean' => 9.8,  'std_dev' => 2.7], 'O2_EST' => ['mean' => 10.4, 'std_dev' => 2.6],
            'O3_SEN' => ['mean' => 10.9, 'std_dev' => 2.4], 'O4_ACT' => ['mean' => 9.5,  'std_dev' => 2.6],
            'O5_IDE' => ['mean' => 10.1, 'std_dev' => 2.9], 'O6_VAL' => ['mean' => 9.1,  'std_dev' => 2.7],
            'C1_COM' => ['mean' => 11.4, 'std_dev' => 2.4], 'C2_ORD' => ['mean' => 9.8,  'std_dev' => 2.8],
            'C3_DEV' => ['mean' => 11.6, 'std_dev' => 2.3], 'C4_REA' => ['mean' => 10.7, 'std_dev' => 2.5],
            'C5_DIS' => ['mean' => 10.2, 'std_dev' => 2.7], 'C6_DEL' => ['mean' => 9.7,  'std_dev' => 2.6],
            'E1_CHA' => ['mean' => 11.0, 'std_dev' => 2.4], 'E2_GRE' => ['mean' => 9.4,  'std_dev' => 2.8],
            'E3_ASS' => ['mean' => 9.6,  'std_dev' => 2.7], 'E4_ACT' => ['mean' => 10.1, 'std_dev' => 2.6],
            'E5_STI' => ['mean' => 8.2,  'std_dev' => 2.8], 'E6_EMO' => ['mean' => 10.7, 'std_dev' => 2.5],
            'A1_CON' => ['mean' => 10.6, 'std_dev' => 2.5], 'A2_DRO' => ['mean' => 11.4, 'std_dev' => 2.3],
            'A3_ALT' => ['mean' => 11.7, 'std_dev' => 2.2], 'A4_COM' => ['mean' => 9.9,  'std_dev' => 2.6],
            'A5_MOD' => ['mean' => 10.0, 'std_dev' => 2.7], 'A6_SEN' => ['mean' => 11.0, 'std_dev' => 2.4],
            'N1_ANX' => ['mean' => 8.8,  'std_dev' => 3.0], 'N2_HOS' => ['mean' => 7.4,  'std_dev' => 2.7],
            'N3_DEP' => ['mean' => 7.8,  'std_dev' => 2.9], 'N4_CON' => ['mean' => 8.6,  'std_dev' => 2.9],
            'N5_IMP' => ['mean' => 9.4,  'std_dev' => 2.7], 'N6_VUL' => ['mean' => 7.5,  'std_dev' => 2.8],
        ];
    }
}
