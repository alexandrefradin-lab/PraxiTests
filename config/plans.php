<?php

/**
 * Plans d'abonnement PraxiQuest
 *
 * stripe_price_id : ID du Price Stripe (ex: price_xxx)
 * Créer les produits & prices dans le dashboard Stripe,
 * puis renseigner les IDs ici (ou via les variables d'env).
 */
return [

    'default_trial_days' => 14,

    'plans' => [

        'starter' => [
            'name'           => 'Starter',
            'description'    => 'Parfait pour démarrer — accès à tous les tests de base.',
            'price_monthly'  => 1900,   // centimes
            'price_yearly'   => 19000,
            'stripe_monthly' => env('STRIPE_PRICE_STARTER_MONTHLY', ''),
            'stripe_yearly'  => env('STRIPE_PRICE_STARTER_YEARLY', ''),
            'features'       => [
                '5 tests disponibles',
                'Synthèse IA (résumé court)',
                'Rapport PDF',
                'Support email',
            ],
            'highlighted'    => false,
            'color'          => '#6B7280',
        ],

        'pro' => [
            'name'           => 'Pro',
            'description'    => 'L\'essentiel pour un accompagnement complet.',
            'price_monthly'  => 4900,
            'price_yearly'   => 49000,
            'stripe_monthly' => env('STRIPE_PRICE_PRO_MONTHLY', ''),
            'stripe_yearly'  => env('STRIPE_PRICE_PRO_YEARLY', ''),
            'features'       => [
                'Tous les tests disponibles',
                'Synthèse IA complète + 15 métiers',
                'Rapports PDF illimités',
                'Invitations candidats (50/mois)',
                'Tableau de bord pro',
                'Support prioritaire',
            ],
            'highlighted'    => true,
            'color'          => '#1B2A4A',
        ],

        'enterprise' => [
            'name'           => 'Enterprise',
            'description'    => 'Pour les cabinets et organismes à fort volume.',
            'price_monthly'  => 9900,
            'price_yearly'   => 99000,
            'stripe_monthly' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY', ''),
            'stripe_yearly'  => env('STRIPE_PRICE_ENTERPRISE_YEARLY', ''),
            'features'       => [
                'Tout Pro +',
                'Invitations illimitées',
                'White-label (logo + couleurs)',
                'Export données (CSV / API)',
                'Compte multi-utilisateurs',
                'SLA + support dédié',
            ],
            'highlighted'    => false,
            'color'          => '#B8860B',
        ],

    ],

];
