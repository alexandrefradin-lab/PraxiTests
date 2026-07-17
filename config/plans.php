<?php

/**
 * Plans d'abonnement PraxiQuest — grille V1 (étude de marché 2026-07-16).
 *
 * stripe_monthly / stripe_yearly : IDs des Prices Stripe (ex: price_xxx).
 * Créer les produits & prices dans le dashboard Stripe, puis renseigner
 * les IDs via les variables d'env.
 *
 * available : false = palier affiché « Bientôt disponible », non souscriptible
 *   (Cabinet/Centre attendent le multi-comptes structure — ProfessionalAccount).
 * quota_dossiers : nombre d'invitations candidat par mois calendaire. Appliqué
 *   uniquement quand le paywall est actif (praxiquest.billing.enforced).
 */
return [

    'default_trial_days' => 14,

    'plans' => [

        'independant' => [
            'name'           => 'Indépendant',
            'description'    => 'Pour le consultant en bilan de compétences : tout le produit, un prix simple.',
            'price_monthly'  => 3900,   // centimes
            'price_yearly'   => 39000,  // 2 mois offerts
            'stripe_monthly' => env('STRIPE_PRICE_INDEPENDANT_MONTHLY', ''),
            'stripe_yearly'  => env('STRIPE_PRICE_INDEPENDANT_YEARLY', ''),
            'available'      => true,
            'quota_dossiers' => 5,
            'features'       => [
                '5 dossiers candidats / mois — tous les tests inclus',
                '12 tests psychométriques (RIASEC, Big Five, valeurs, EQ-i, stress, biais cognitifs, TDAH, hypersensibilité, 360°, gestion du temps, entrepreneuriat, orientation express)',
                'Synthèse IA rédigée + pistes métiers + plans d\'action',
                'Grimoire, Oracle et parcours d\'accompagnement 30-60 jours',
                'Rapports PDF à votre marque',
                'Invitations en un lien, relances et campagnes email',
                'Exports CSV (dossiers Qualiopi)',
            ],
            'highlighted'    => true,
            'color'          => '#A67520',
        ],

        'cabinet' => [
            'name'           => 'Cabinet',
            'description'    => 'Pour les cabinets de 2 à 5 consultants — comptes d\'équipe et vue consolidée.',
            'price_monthly'  => 9900,
            'price_yearly'   => 99000,
            'stripe_monthly' => env('STRIPE_PRICE_CABINET_MONTHLY', ''),
            'stripe_yearly'  => env('STRIPE_PRICE_CABINET_YEARLY', ''),
            'available'      => false, // en attente du multi-comptes (ProfessionalAccount)
            'quota_dossiers' => 20,
            'features'       => [
                '20 dossiers candidats / mois',
                '3 comptes consultants',
                'Tout Indépendant inclus',
                'Vue consolidée du cabinet',
            ],
            'highlighted'    => false,
            'color'          => '#1B2A4A',
        ],

        'centre' => [
            'name'           => 'Centre',
            'description'    => 'Pour les centres et réseaux multi-sites — sur devis.',
            'price_monthly'  => 24900,
            'price_yearly'   => 249000,
            'stripe_monthly' => env('STRIPE_PRICE_CENTRE_MONTHLY', ''),
            'stripe_yearly'  => env('STRIPE_PRICE_CENTRE_YEARLY', ''),
            'available'      => false, // en attente du multi-comptes (ProfessionalAccount)
            'quota_dossiers' => 60,
            'features'       => [
                '60 dossiers candidats / mois',
                'Comptes consultants illimités',
                'Tout Cabinet inclus',
                'Onboarding accompagné',
            ],
            'highlighted'    => false,
            'color'          => '#7B1515',
        ],

    ],

];
