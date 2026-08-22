<?php

/**
 * Offre particuliers (B2C) — achat one-shot « Rapport complet ».
 *
 * Un candidat AUTO-INSCRIT (venu de la landing, sans invitation d'un
 * professionnel) découvre gratuitement l'épreuve d'appel (free_test_slugs),
 * puis débloque tout le parcours par un paiement unique. Les candidats
 * invités par un professionnel ne sont JAMAIS concernés : leur accès reste
 * porté par l'abonnement du professionnel (config/plans.php).
 *
 * enforced : interrupteur du paywall particulier, même logique que
 *   praxiquest.billing.enforced. false (défaut) = comportement historique,
 *   tout est ouvert. Bascule le jour J via .env : B2C_PAYWALL_ENFORCED=true
 *   puis php artisan config:cache. Prérequis : produits/prix créés dans le
 *   dashboard Stripe et Price IDs renseignés ci-dessous.
 */
return [

    'enforced' => env('B2C_PAYWALL_ENFORCED', false),

    // Épreuves d'appel, jouables gratuitement par un auto-inscrit.
    'free_test_slugs' => ['orientation-express'],

    'products' => [

        'rapport' => [
            'name'         => 'Rapport complet',
            'description'  => 'Toutes les épreuves, la relecture globale par IA et ton rapport PDF.',
            'price'        => 4900, // centimes TTC
            'stripe_price' => env('STRIPE_PRICE_B2C_RAPPORT', ''),
            'available'    => true,
            'features'     => [
                'Toutes les épreuves fondées sur des modèles reconnus',
                'Le Grimoire : relecture globale de ton profil par IA',
                'Jusqu\'à 50 horizons métiers + plans d\'action',
                'Ton rapport PDF complet, téléchargeable',
                'Les modules d\'entraînement gagnés au fil des épreuves',
            ],
            'highlighted'  => true,
        ],

        'rapport_debrief' => [
            'name'         => 'Rapport + Débrief',
            'description'  => 'Le rapport complet, puis 1 h d\'échange avec un professionnel de l\'accompagnement.',
            'price'        => 12900,
            'stripe_price' => env('STRIPE_PRICE_B2C_DEBRIEF', ''),
            // Nécessite le réseau de professionnels partenaires — activer via .env
            // quand le circuit de mise en relation est prêt.
            'available'    => env('B2C_DEBRIEF_AVAILABLE', false),
            'features'     => [
                'Tout le Rapport complet',
                '1 h de débrief individuel avec un professionnel',
                'Tes résultats expliqués de vive voix, tes questions posées',
            ],
            'highlighted'  => false,
        ],

    ],
];
