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
        'cv_allowed_mimes' => ['pdf', 'doc', 'docx'],
    ],

    'results' => [
        'suggested_jobs_count' => 15,
        'min_jobs_count'       => 10,
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
        'primary_color'   => env('PRAXIQUEST_COLOR_PRIMARY', '#4F46E5'),
        'secondary_color' => env('PRAXIQUEST_COLOR_SECONDARY', '#10B981'),
    ],
];
