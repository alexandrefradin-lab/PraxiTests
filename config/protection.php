<?php

/*
|--------------------------------------------------------------------------
| Protection anti-copie — configuration unique des 4 volets
|--------------------------------------------------------------------------
| 1. license   : refuse de servir l'application si le code est redéployé sur
|                un domaine non licencié (vol de code, backup, prestataire).
| 2. scraping  : détecte l'aspiration du contenu des tests (questionnaires,
|                barèmes, restitutions) et bloque l'auteur.
| 3. sharing   : détecte le partage / la revente d'un compte professionnel
|                (multi-appareils, multi-IP, cadence anormale).
| 4. watermark : tatoue les rapports PDF avec un identifiant de traçage qui
|                permet de remonter au compte à l'origine d'une fuite.
|
| PRUDENCE : tous les volets bloquants sont désactivés par défaut. On les
| active un par un via .env, d'abord en mode 'warn' (journalisation seule),
| puis en mode 'block' une fois les faux positifs observés à zéro.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | 1. Licence — verrouillage du code au domaine
    |--------------------------------------------------------------------------
    | La licence est un jeton signé (RSA-SHA256) contenant les domaines
    | autorisés et une date d'expiration. Seul le détenteur de la clé PRIVÉE
    | (toi, hors serveur) peut en émettre une ; le serveur ne porte que la clé
    | publique. Un code volé et redéployé sur un autre domaine ne démarre pas.
    |
    | Mise en place :
    |   php artisan praxiquest:license:keygen        (une fois, sur ta machine)
    |   php artisan praxiquest:license:issue ...     (émet le jeton)
    |   PRAXIQUEST_LICENSE_KEY=... dans le .env du serveur
    */
    'license' => [
        'enabled' => env('PRAXIQUEST_LICENSE_ENFORCED', false),

        // Jeton signé, collé tel quel depuis praxiquest:license:issue.
        'key' => env('PRAXIQUEST_LICENSE_KEY'),

        // Clé publique de vérification (PEM). Volontairement en dur : une clé
        // publique lue depuis .env serait remplaçable par le voleur en même
        // temps que le jeton. À remplir après praxiquest:license:keygen.
        'public_key' => <<<'PEM'
        -----BEGIN PUBLIC KEY-----
        REMPLACER_PAR_LA_SORTIE_DE_PRAXIQUEST_LICENSE_KEYGEN
        -----END PUBLIC KEY-----
        PEM,

        // warn  : journalise l'anomalie, laisse passer (phase d'observation).
        // block : renvoie une 503 sur toutes les routes non exemptées.
        'mode' => env('PRAXIQUEST_LICENSE_MODE', 'warn'),

        // Jours de tolérance après expiration avant blocage effectif — évite
        // de couper la production un dimanche pour un renouvellement oublié.
        'grace_days' => (int) env('PRAXIQUEST_LICENSE_GRACE_DAYS', 14),

        // Toujours servies, même licence invalide : health-check infra,
        // webhook Stripe (sinon on perd des événements de facturation) et
        // pages légales (obligation de publication).
        'exempt_paths' => ['up', 'stripe/*', 'mentions-legales', 'cgv', 'confidentialite'],

        // Durée de mise en cache du résultat de vérification (secondes).
        'cache_ttl' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | 2. Anti-scraping — protection du contenu des tests
    |--------------------------------------------------------------------------
    | Le patrimoine de PraxiQuest, ce sont les questionnaires, les barèmes et
    | les restitutions. Un concurrent peut les aspirer en parcourant le front
    | avec un compte gratuit. On mesure ici la cadence de consultation du
    | contenu protégé sur une fenêtre glissante.
    */
    'scraping' => [
        'enabled' => env('PRAXIQUEST_ANTISCRAPING_ENABLED', true),

        // warn | block — 'warn' n'alerte que, 'block' renvoie une 429.
        'mode' => env('PRAXIQUEST_ANTISCRAPING_MODE', 'warn'),

        // Seuils par fenêtre glissante. Un candidat honnête consulte quelques
        // dizaines de pages de contenu par heure ; un aspirateur, des milliers.
        'window_minutes' => 10,
        'max_hits' => (int) env('PRAXIQUEST_ANTISCRAPING_MAX_HITS', 120),

        // Durée du blocage après franchissement du seuil (minutes).
        'block_minutes' => 60,

        // User-agents d'outils d'aspiration connus — bloqués sans attendre le
        // seuil. Un vrai navigateur ne s'annonce jamais ainsi.
        'blocked_agents' => [
            'curl', 'wget', 'python-requests', 'httpie', 'scrapy', 'httrack',
            'go-http-client', 'java/', 'libwww-perl', 'axios/', 'node-fetch',
            'headlesschrome', 'phantomjs', 'puppeteer', 'playwright',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 3. Partage de comptes — un abonnement, une structure
    |--------------------------------------------------------------------------
    | Un organisme qui paie une licence et fait passer les tests de dix autres
    | structures est la fuite de revenu la plus courante d'un SaaS B2B. On
    | empreinte les appareils par compte et on alerte au-delà du raisonnable.
    */
    'sharing' => [
        'enabled' => env('PRAXIQUEST_SHARING_DETECTION_ENABLED', true),

        // warn | block — 'block' déconnecte les sessions au-delà du plafond.
        'mode' => env('PRAXIQUEST_SHARING_MODE', 'warn'),

        // Appareils distincts tolérés par compte sur la fenêtre d'observation.
        // Un consultant travaille sur 2 à 3 appareils (bureau, portable, mobile).
        'max_devices' => (int) env('PRAXIQUEST_SHARING_MAX_DEVICES', 5),
        'window_days' => 30,

        // Réseaux distincts (préfixe /24 de l'IP) tolérés sur 24 h. Au-delà,
        // le compte circule entre des lieux qui n'ont rien à voir.
        'max_networks_per_day' => (int) env('PRAXIQUEST_SHARING_MAX_NETWORKS', 8),

        // Les comptes candidats sont exemptés : ils n'ont rien à revendre, et
        // un candidat qui passe ses tests au bureau puis chez lui n'est pas
        // suspect. Seuls les comptes professionnels sont surveillés.
        'watched_roles' => ['professional'],

        // Délai minimal entre deux écritures en base pour un même appareil.
        // Sans cela, on ferait un UPDATE à chaque requête HTTP.
        'touch_interval_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | 4. Traçage des PDF — remonter à la source d'une fuite
    |--------------------------------------------------------------------------
    | Chaque rapport porte un identifiant dérivé du couple (compte, document).
    | Si un PDF ressort là où il ne devrait pas, l'identifiant désigne le
    | compte qui l'a téléchargé. Actif par défaut : sans effet de bord.
    */
    'watermark' => [
        'enabled' => env('PRAXIQUEST_PDF_WATERMARK_ENABLED', true),

        // Mention visible en pied de page (nom + identifiant de traçage).
        'visible' => env('PRAXIQUEST_PDF_WATERMARK_VISIBLE', true),

        // Journalise chaque téléchargement (qui, quoi, quand, depuis où).
        'log_downloads' => true,
    ],

];
