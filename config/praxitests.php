<?php

return [
    'version' => '1.0.0-alpha',

    'license' => [
        'key' => env('PRAXITESTS_LICENSE_KEY'),
        'api' => env('PRAXITESTS_LICENSE_API'),
    ],

    'features' => [
        'gamification' => env('PRAXITESTS_GAMIFICATION_ENABLED', true),
        'neuromarketing' => env('PRAXITESTS_NEUROMARKETING_ENABLED', true),
        'multitenant' => env('PRAXITESTS_MULTITENANT', true),
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
        'name'    => env('APP_NAME', 'PraxiTests'),
        'tagline' => 'Évaluer. Orienter. Transformer.',
        'logo'    => env('PRAXITESTS_LOGO_URL'),
        'primary_color'   => env('PRAXITESTS_COLOR_PRIMARY', '#4F46E5'),
        'secondary_color' => env('PRAXITESTS_COLOR_SECONDARY', '#10B981'),
    ],
];
