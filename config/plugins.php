<?php

return [
    'path' => base_path('plugins'),

    'autodiscover' => env('PRAXIQUEST_PLUGINS_AUTODISCOVER', true),

    'manifest_file' => 'plugin.json',

    /*
    |--------------------------------------------------------------------------
    | Dossiers exclus de la découverte automatique
    |--------------------------------------------------------------------------
    | Les dossiers dont le nom est dans cette liste (ou commençant par _)
    | ne seront jamais chargés comme plugins.
    */
    'excluded_directories' => [
        '_template',
        'stubs',
        'examples',
        '_examples',
    ],

    'cache_key' => 'praxiquest.plugins.registry',

    'available_types' => [
        'test',          // ajoute des types de tests
        'scoring',       // moteurs de scoring
        'ai',            // drivers IA
        'mail',          // drivers mail / templates
        'storage',       // drivers storage
        'gamification',  // mécaniques jeu
        'integration',   // CRM, marketing, etc.
        'theme',         // white-label
        'reporting',     // exports
    ],

    'available_permissions' => [
        'read:profiles',
        'write:profiles',
        'read:tests',
        'write:tests',
        'read:results',
        'write:results',
        'send:mail',
        'manage:plugins',
        'access:admin',
    ],

    'core_plugins' => [
        // plugins fournis dans la base
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces autorisés pour les service providers de plugins (SEC-05)
    |--------------------------------------------------------------------------
    | Seuls les FQCN commençant par l'un de ces préfixes seront acceptés.
    | Ajouter ici tout namespace tiers de confiance si besoin.
    */
    'allowed_namespaces' => [
        'Praxis\\Plugins\\',
    ],
];
