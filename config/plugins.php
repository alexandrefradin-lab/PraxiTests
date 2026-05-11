<?php

return [
    'path' => base_path('plugins'),

    'autodiscover' => env('PRAXITESTS_PLUGINS_AUTODISCOVER', true),

    'manifest_file' => 'plugin.json',

    'cache_key' => 'praxitests.plugins.registry',

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
];
