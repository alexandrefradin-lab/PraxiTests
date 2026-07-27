<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tâches planifiées PraxiQuest
|--------------------------------------------------------------------------
|
| OVH — Ajouter UN SEUL cron dans le Panneau OVH (Hébergements > Tâches cron) :
|
|   Commande  : php /home/CLUSTER/DOMAINE/www/praxiquest/artisan schedule:run
|   Fréquence : toutes les minutes (ou toutes les 5 min selon OVH)
|   PHP        : 8.2
|
| Ce cron déclenche le scheduler Laravel qui exécute tout ci-dessous.
|
*/

// ── Queue : traite les jobs en attente (IA, CV, emails) ──────────────────
// Toutes les minutes — s'arrête seul quand la queue est vide.
// Limite à 20 jobs par passage pour rester dans le timeout OVH (60s).
Schedule::command('queue:work database --stop-when-empty --timeout=50 --max-jobs=20')
    ->everyMinute()
    ->withoutOverlapping(1)   // ne se lance pas si un passage est encore en cours
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/queue.log'));

// ── Recalcul des normes d'étalonnage ─────────────────────────────────────
// Hebdomadaire — recalcule les normes plateforme (origin='platform') de TOUS
// les tests ayant des résultats : globales + par tranche d'âge. Les dimensions
// sont découvertes depuis les norm_scores persistés, plus de liste en dur.
// Les normes de référence seedées ne sont jamais écrasées.
Schedule::call(function () {
    $written = \Praxis\Core\TestEngine\NormInterpreter::recomputeAll();
    logger()->info('norms:recompute — ' . (empty($written) ? 'aucune norme actualisée (seuils non atteints)' : json_encode($written)));
})->weekly()->name('norms:recompute')->withoutOverlapping();

// ── Relance automatique des synthèses IA bloquées ────────────────────────
// Toutes les 5 min : détecte les zombies (process PHP tué) et les échecs
// marqués (ai_failed=true) et les envoie sur la queue database pour que
// le worker ci-dessus les traite au prochain passage.
// Cooldown par attempt de 10 min (Cache) pour éviter les boucles infinies
// si l'IA est durablement indisponible.
Schedule::command('insights:retry-zombies')
    ->everyFiveMinutes()
    ->name('insights:retry-zombies')
    ->withoutOverlapping(2)
    ->appendOutputTo(storage_path('logs/insights_retry.log'));

// ── Nettoyage des tentatives abandonnées ─────────────────────────────────
// Marque "abandoned" les tentatives sans activité depuis plus de 30 jours.
Schedule::call(function () {
    \App\Models\TestAttempt::where('status', 'in_progress')
        ->where('last_activity_at', '<', now()->subDays(30))
        ->update(['status' => 'abandoned']);
})->daily()->name('attempts:cleanup');

// ── Relances parcours journaliers ────────────────────────────────────────────
// Chaque soir à 20h : envoie un email de questionnement sur les croyances
// bloquantes aux utilisateurs n'ayant pas accompli leur action du jour
// (praxilead, praxizenith, praxivision).
Schedule::command('journey:nudge')
    ->dailyAt('20:00')
    ->name('journey:nudge')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/journey_nudge.log'));

// ── Nettoyage des invitations expirées ────────────────────────────────────
Schedule::call(function () {
    \App\Models\TestInvitation::where('status', 'pending')
        ->where('expires_at', '<', now())
        ->update(['status' => 'expired']);
})->daily()->name('invitations:expire');
