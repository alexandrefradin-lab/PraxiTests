<?php

/*
 * Clés des services tiers. Lues via config() (jamais env() en dehors des
 * fichiers de config : env() renvoie null quand la config est cachée en prod).
 */
return [

    'brevo' => [
        // API transactionnelle Brevo (transport mail 'brevo') — xkeysib-…
        'key' => env('BREVO_API_KEY'),
    ],

];
