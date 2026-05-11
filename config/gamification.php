<?php

return [
    'enabled' => env('PRAXITESTS_GAMIFICATION_ENABLED', true),

    'xp' => [
        'answer_question'   => 10,
        'complete_section'  => 50,
        'complete_test'     => 200,
        'first_test'        => 100,
        'cv_uploaded'       => 50,
        'unlock_insight'    => 25,
    ],

    'levels' => [
        ['level' => 1, 'name' => 'Curieux',   'xp_required' => 0],
        ['level' => 2, 'name' => 'Explorateur', 'xp_required' => 200],
        ['level' => 3, 'name' => 'Analyste',   'xp_required' => 500],
        ['level' => 4, 'name' => 'Stratège',   'xp_required' => 1000],
        ['level' => 5, 'name' => 'Visionnaire', 'xp_required' => 2000],
    ],

    'badges' => [
        'first_step'      => ['name' => 'Premier pas', 'icon' => 'rocket'],
        'completionist'   => ['name' => 'Complétiste', 'icon' => 'check-circle'],
        'speedrunner'     => ['name' => 'Rapide comme l\'éclair', 'icon' => 'bolt'],
        'introspective'   => ['name' => 'Introspectif',   'icon' => 'eye'],
        'decision_maker'  => ['name' => 'Décideur',       'icon' => 'compass'],
        'analyzer'        => ['name' => 'Analyste',       'icon' => 'chart-bar'],
        'storyteller'     => ['name' => 'Conteur',        'icon' => 'book-open'],
    ],

    'mechanics' => [
        'progress_bar' => true,
        'live_feedback' => true,
        'narrative' => true,
        'score_comparison' => true,
        'unlockable_insights' => true,
        'celebration_animations' => true,
    ],

    'narrative' => [
        'intro'      => 'Bienvenue dans l\'exploration de ton profil. Chaque réponse révèle un peu plus de ton potentiel.',
        'midway'     => 'Tu progresses bien. La cartographie de ton potentiel se dessine.',
        'final'      => 'Dernière étape. Ton profil unique va prendre forme.',
        'completion' => 'Ton profil est cartographié. Voyons ce qui t\'attend.',
    ],
];
