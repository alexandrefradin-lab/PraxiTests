<?php

return [
    'enabled' => env('PRAXIQUEST_NEUROMARKETING_ENABLED', true),

    'biases' => [
        'scarcity'    => true,  // places limitées
        'urgency'     => true,  // deadlines
        'social_proof' => true, // ils ont déjà passé le test
        'authority'   => true,  // recommandé par...
        'reciprocity' => true,  // contenu offert en échange
        'commitment'  => true,  // étapes engageantes
        'anchoring'   => true,  // prix barré, comparaison
        'zeigarnik'   => true,  // effet de complétion
    ],

    'email_optimization' => [
        'subject_variants' => 3,        // A/B/C subject lines
        'preheader_variants' => 2,
        'cta_variants' => 2,
        'send_time_optimization' => true,
        'tone_personalization' => true,
    ],

    'page_optimization' => [
        'cta_emphasis' => true,
        'progress_indicators' => true,
        'completion_streaks' => true,
        'unlock_animations' => true,
    ],

    'funnels' => [
        'lead_to_test'      => ['biases' => ['scarcity', 'social_proof', 'reciprocity']],
        'test_completion'   => ['biases' => ['zeigarnik', 'commitment']],
        'result_to_premium' => ['biases' => ['anchoring', 'urgency', 'authority']],
    ],
];
