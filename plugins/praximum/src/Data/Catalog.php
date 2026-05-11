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
            'O1_FAN' => ['label' => 'Fantaisie',              'dim' => 'O'],
            'O2_EST' => ['label' => 'Esthétique',             'dim' => 'O'],
            'O3_SEN' => ['label' => 'Sentiments',             'dim' => 'O'],
            'O4_ACT' => ['label' => 'Actions',                'dim' => 'O'],
            'O5_IDE' => ['label' => 'Idées',                  'dim' => 'O'],
            'O6_VAL' => ['label' => 'Valeurs',                'dim' => 'O'],
            'C1_COM' => ['label' => 'Compétence',             'dim' => 'C'],
            'C2_ORD' => ['label' => 'Ordre',                  'dim' => 'C'],
            'C3_DEV' => ['label' => 'Sens du devoir',         'dim' => 'C'],
            'C4_REA' => ['label' => 'Recherche de réussite',  'dim' => 'C'],
            'C5_DIS' => ['label' => 'Autodiscipline',         'dim' => 'C'],
            'C6_DEL' => ['label' => 'Délibération',           'dim' => 'C'],
            'E1_CHA' => ['label' => 'Chaleur',                'dim' => 'E'],
            'E2_GRE' => ['label' => 'Grégarité',              'dim' => 'E'],
            'E3_ASS' => ['label' => 'Assertivité',            'dim' => 'E'],
            'E4_ACT' => ['label' => 'Activité',               'dim' => 'E'],
            'E5_STI' => ['label' => 'Recherche de sensations','dim' => 'E'],
            'E6_EMO' => ['label' => 'Émotions positives',     'dim' => 'E'],
            'A1_CON' => ['label' => 'Confiance',              'dim' => 'A'],
            'A2_DRO' => ['label' => 'Droiture',               'dim' => 'A'],
            'A3_ALT' => ['label' => 'Altruisme',              'dim' => 'A'],
            'A4_COM' => ['label' => 'Compliance',             'dim' => 'A'],
            'A5_MOD' => ['label' => 'Modestie',               'dim' => 'A'],
            'A6_SEN' => ['label' => 'Sensibilité',            'dim' => 'A'],
            'N1_ANX' => ['label' => 'Anxiété',                'dim' => 'N'],
            'N2_HOS' => ['label' => 'Hostilité',              'dim' => 'N'],
            'N3_DEP' => ['label' => 'Dépression',             'dim' => 'N'],
            'N4_CON' => ['label' => 'Conscience de soi',      'dim' => 'N'],
            'N5_IMP' => ['label' => 'Impulsivité',            'dim' => 'N'],
            'N6_VUL' => ['label' => 'Vulnérabilité',          'dim' => 'N'],
        ];
    }

    /** Normes population française (mean/sd) — issu du WP plugin v1.4.1. */
    public static function normes(): array
    {
        return [
            'O1_FAN' => ['mean' => 9.8,  'sd' => 2.7], 'O2_EST' => ['mean' => 10.4, 'sd' => 2.6],
            'O3_SEN' => ['mean' => 10.9, 'sd' => 2.4], 'O4_ACT' => ['mean' => 9.5,  'sd' => 2.6],
            'O5_IDE' => ['mean' => 10.1, 'sd' => 2.9], 'O6_VAL' => ['mean' => 9.1,  'sd' => 2.7],
            'C1_COM' => ['mean' => 11.4, 'sd' => 2.4], 'C2_ORD' => ['mean' => 9.8,  'sd' => 2.8],
            'C3_DEV' => ['mean' => 11.6, 'sd' => 2.3], 'C4_REA' => ['mean' => 10.7, 'sd' => 2.5],
            'C5_DIS' => ['mean' => 10.2, 'sd' => 2.7], 'C6_DEL' => ['mean' => 9.7,  'sd' => 2.6],
            'E1_CHA' => ['mean' => 11.0, 'sd' => 2.4], 'E2_GRE' => ['mean' => 9.4,  'sd' => 2.8],
            'E3_ASS' => ['mean' => 9.6,  'sd' => 2.7], 'E4_ACT' => ['mean' => 10.1, 'sd' => 2.6],
            'E5_STI' => ['mean' => 8.2,  'sd' => 2.8], 'E6_EMO' => ['mean' => 10.7, 'sd' => 2.5],
            'A1_CON' => ['mean' => 10.8, 'sd' => 2.5], 'A2_DRO' => ['mean' => 11.2, 'sd' => 2.4],
            'A3_ALT' => ['mean' => 11.6, 'sd' => 2.3], 'A4_COM' => ['mean' => 9.4,  'sd' => 2.8],
            'A5_MOD' => ['mean' => 9.8,  'sd' => 2.7], 'A6_SEN' => ['mean' => 11.2, 'sd' => 2.4],
            'N1_ANX' => ['mean' => 9.2,  'sd' => 2.7], 'N2_HOS' => ['mean' => 7.8,  'sd' => 2.6],
            'N3_DEP' => ['mean' => 7.9,  'sd' => 2.9], 'N4_CON' => ['mean' => 8.6,  'sd' => 2.9],
            'N5_IMP' => ['mean' => 9.4,  'sd' => 2.7], 'N6_VUL' => ['mean' => 7.5,  'sd' => 2.8],
        ];
    }
}
