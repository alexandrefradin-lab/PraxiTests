<?php

return [
    'version' => '1.0.0-alpha',

    'license' => [
        'key' => env('PRAXIQUEST_LICENSE_KEY'),
        'api' => env('PRAXIQUEST_LICENSE_API'),
    ],

    'features' => [
        'gamification' => env('PRAXIQUEST_GAMIFICATION_ENABLED', true),
        'neuromarketing' => env('PRAXIQUEST_NEUROMARKETING_ENABLED', true),
        'multitenant' => env('PRAXIQUEST_MULTITENANT', false), // non implémenté — activer manuellement
    ],

    'profile' => [
        'statuses' => [
            'employee'    => 'Salarié',
            'entrepreneur' => 'Entrepreneur',
            'jobseeker'   => 'Demandeur d\'emploi',
            'student'     => 'Étudiant',
            'other'       => 'Autre',
        ],
        'cv_required' => true,
        'cv_max_size_kb' => 5120,
        // A4 — doc/docx retirés : CvExtractionService ne les extrait pas (prévoir phpoffice/phpword)
        'cv_allowed_mimes' => ['pdf'],
    ],

    'results' => [
        'suggested_jobs_count' => 30,
        'min_jobs_count'       => 10,
        // Pistes métiers dynamiques (PTP) — nombre affiché dans le Grimoire global.
        'career_paths_count'        => 30,
        // Pistes affichées sous chaque page de résultats de test (≤ 1 an, éphémères).
        'career_paths_per_test'     => 30,
    ],

    'install' => [
        'flag_file' => storage_path('app/.installed'),
        'min_php'   => '8.2.0',
        'required_extensions' => ['pdo', 'mbstring', 'openssl', 'fileinfo', 'json', 'tokenizer', 'xml', 'ctype'],
    ],

    'branding' => [
        'name'    => env('APP_NAME', 'PraxiQuest'),
        'tagline' => 'Évaluer. Orienter. Transformer.',
        'logo'    => env('PRAXIQUEST_LOGO_URL'),
        'primary_color'   => env('PRAXIQUEST_COLOR_PRIMARY', '#A67520'),   // Or de la Fraternité
        'secondary_color' => env('PRAXIQUEST_COLOR_SECONDARY', '#7B1515'), // Cramoisi
    ],

    /*
    |--------------------------------------------------------------------------
    | Rapport PDF — personnalisation
    |--------------------------------------------------------------------------
    | Pilote le rendu de resources/views/pdf/results.blade.php.
    | - sections : activer / désactiver chaque bloc du rapport
    | - footer   : coordonnées du cabinet / conseiller + mention légale
    | - paper    : format & orientation DomPDF
    | Les couleurs et le logo sont repris depuis 'branding' ci-dessus, et peuvent
    | être surchargés par tenant via la table settings (group = 'pdf').
    */
    'pdf' => [
        'paper'       => env('PRAXIQUEST_PDF_PAPER', 'a4'),
        'orientation' => env('PRAXIQUEST_PDF_ORIENTATION', 'portrait'),

        'sections' => [
            'cover'      => true,
            'profile'    => true,
            'synthesis'  => true,
            'strengths'  => true,
            'dimensions' => true,
            'jobs'       => true,
            'footer'     => true,
        ],

        'footer' => [
            'advisor' => env('PRAXIQUEST_PDF_ADVISOR'),
            'email'   => env('PRAXIQUEST_PDF_EMAIL'),
            'phone'   => env('PRAXIQUEST_PDF_PHONE'),
            'website' => env('PRAXIQUEST_PDF_WEBSITE'),
            'address' => env('PRAXIQUEST_PDF_ADDRESS'),
            'legal'   => 'Document confidentiel — usage personnel. Données traitées conformément au RGPD.',
        ],
    ],
];
