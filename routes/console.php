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
// Hebdomadaire — met à jour les normes depuis les vraies données plateforme
// dès que le seuil de 50 passations par test est atteint.
Schedule::call(function () {
    $tests = [
        ['praximet-riasec',     ['R','I','A','S','E','C']],
        ['praxiemo-eqi',        ['1','4','9','16','2','3','5','6','7','8','10','11','12','13','14','15']],
        ['praxivaleurs-schwartz',['autonomie','stimulation','hedonisme','reussite','pouvoir','conformite','tradition','bienveillance','universalisme','securite']],
        ['praximum-bigfive',    ['O','C','E','A','N']],
    ];
    foreach ($tests as [$slug, $dims]) {
        foreach ($dims as $dim) {
            \Praxis\Core\TestEngine\NormInterpreter::recompute($slug, $dim, 50);
        }
    }
})->weekly()->name('norms:recompute')->withoutOverlapping();

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
// (praxilead, praxizenith).
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
