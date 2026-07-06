<?php

/*
 * Déclencheur du scheduler Laravel via le cron OVH (manager → Hébergement →
 * Cron : « praxiquest/cron-scheduler.php », PHP 8.2, toutes les heures).
 *
 * OVH mutualisé n'autorise qu'une exécution PAR HEURE (les minutes du crontab
 * sont ignorées). À chaque passage, schedule:run exécute les tâches dues de
 * routes/console.php — dont queue:work --stop-when-empty qui draine la file
 * (campagnes email, synthèses IA en retard, relances…).
 *
 * Les emails à délivrance immédiate (invitations, vérification, reset) sont
 * envoyés en direct pendant la requête web via l'API Brevo — ils ne dépendent
 * PAS de ce cron.
 */

chdir(__DIR__);

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('schedule:run');

echo $kernel->output();

exit($status);
