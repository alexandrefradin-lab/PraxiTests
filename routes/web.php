<?php

use App\Http\Controllers\Candidate\AttemptController;
use App\Http\Controllers\Candidate\PathPlanController;
use App\Http\Controllers\Candidate\DailyTipController;
use App\Http\Controllers\Candidate\GrimoireController;
use App\Http\Controllers\Candidate\JourneyController;
use App\Http\Controllers\Candidate\OnboardingController;
use App\Http\Controllers\Candidate\OracleController;
use App\Http\Controllers\Candidate\ResultController;
use App\Http\Controllers\Candidate\TestController;
use App\Http\Controllers\Candidate\TreasureController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\GdprController;
use App\Http\Controllers\BlockingBeliefsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cgu', [LegalController::class, 'cgu'])->name('cgu');
Route::get('/confidentialite', [LegalController::class, 'confidentialite'])->name('confidentialite');
Route::get('/mentions-legales', [LegalController::class, 'mentions'])->name('mentions');
Route::get('/contact', [LegalController::class, 'contact'])->name('contact');

// Désabonnement marketing (cf. audit E-5) — URL signée, sans compte requis.
Route::get('/email/unsubscribe/{user}', \App\Http\Controllers\UnsubscribeController::class)
    ->middleware('signed')
    ->name('email.unsubscribe');

// NOTE : Le middleware 'subscribed' (EnsureSubscribed) est disponible mais
// non appliqué ici — accès libre en phase bêta. Pour activer le paywall,
// ajouter 'subscribed' au middleware group ci-dessous.
// Groupe candidat — 'verified' impose la confirmation d'email avant l'accès aux
// tests/résultats. Flux complet dans routes/auth.php (verification.*). Kill-switch
// via config praxiquest.security.require_email_verification + exemption staff
// (App\Http\Middleware\EnsureEmailVerified).
Route::middleware(['auth', 'verified'])->group(function () {
    // Onboarding profil (statut, ancienneté, CV)
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Liste des tests dispo
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
    Route::get('/tests/{test:slug}', [TestController::class, 'show'])->name('tests.show');

    // Tentative en cours
    Route::post('/tests/{test:slug}/start', [AttemptController::class, 'start'])->name('attempt.start');
    Route::get('/attempt/{attempt}', [AttemptController::class, 'show'])->name('attempt.show');
    Route::post('/attempt/{attempt}/answer', [AttemptController::class, 'answer'])->name('attempt.answer');
    Route::post('/attempt/{attempt}/complete', [AttemptController::class, 'complete'])->name('attempt.complete');

    // Restitution
    Route::get('/results/{attempt}', [ResultController::class, 'show'])->name('results.show');
    Route::get('/results/{attempt}/status', [ResultController::class, 'status'])->name('results.status');
    Route::get('/results/{attempt}/pdf', [ResultController::class, 'pdf'])->name('results.pdf');

    // Historique des tentatives du candidat
    Route::get('/history', [ResultController::class, 'history'])->name('history');

    // Feedback 360° — le candidat invite ses évaluateurs et suit la collecte
    Route::get('/results/{attempt}/360',              [\App\Http\Controllers\Candidate\Panel360Controller::class, 'manage'])->name('panel360.manage');
    Route::post('/panel/{panel}/invite',              [\App\Http\Controllers\Candidate\Panel360Controller::class, 'invite'])->name('panel360.invite');
    Route::post('/panel/{panel}/send',                [\App\Http\Controllers\Candidate\Panel360Controller::class, 'send'])->middleware('throttle:10,1')->name('panel360.send');
    Route::post('/panel/{panel}/proceed',             [\App\Http\Controllers\Candidate\Panel360Controller::class, 'proceed'])->name('panel360.proceed');
    Route::delete('/panel/invitation/{invitation}',   [\App\Http\Controllers\Candidate\Panel360Controller::class, 'removeInvitation'])->name('panel360.invitation.destroy');

    // La Salle du Trésor — apps offertes en récompense (déblocage par paliers d'Éclats)
    Route::get('/salle-du-tresor', [TreasureController::class, 'index'])->name('treasure.index');

    // Le Grimoire — relecture globale transversale de tous les tests
    Route::get('/grimoire',          [GrimoireController::class, 'show'])->name('grimoire.show');
    Route::get('/grimoire/status',   [GrimoireController::class, 'status'])->name('grimoire.status');
    Route::get('/grimoire/pdf',      [GrimoireController::class, 'pdf'])->name('grimoire.pdf');
    Route::post('/grimoire/refresh', [GrimoireController::class, 'refresh'])
        ->middleware('throttle:3,1')->name('grimoire.refresh');
    // Déclaration d'une formation visée/acquise pour une piste (déblocage déclaratif PTP)
    Route::post('/grimoire/piste/{pathMatch}/declare', [GrimoireController::class, 'declarePiste'])
        ->middleware('throttle:30,1')->name('grimoire.piste.declare');

    // Plans d'action IA par piste métier (Haiku, throttlé — coût IA)
    Route::get('/career-path/{careerPath:slug}/plan',  [PathPlanController::class, 'show'])->name('path-plan.show');
    Route::post('/career-path/{careerPath:slug}/plan', [PathPlanController::class, 'generate'])
        ->middleware('throttle:5,1')->name('path-plan.generate');

    // L'Oracle — chat IA d'orientation (widget flottant). Envoi rate-limité (coût IA).
    Route::get('/oracle/messages',       [OracleController::class, 'history'])->name('oracle.history');
    Route::post('/oracle/messages',      [OracleController::class, 'message'])->middleware('throttle:20,1')->name('oracle.message');
    Route::delete('/oracle/messages',    [OracleController::class, 'clear'])->name('oracle.clear');

    // Easter egg — Konami Code
    Route::post('/easter-egg/claim', [EasterEggController::class, 'claim'])
        ->middleware('throttle:5,1')->name('easter-egg.claim');

    // Journey 60 jours
    Route::post('/journey/complete', [JourneyController::class, 'completeDay'])->name('journey.complete');
    Route::get('/journey/today',     [JourneyController::class, 'todayData'])->name('journey.today');

    // Tableau de bord de parcours 60 jours (mutualisé — JourneyRegistry).
    Route::get('/parcours/{plugin}',              [\App\Http\Controllers\Candidate\JourneyDashboardController::class, 'index'])->name('journey.index');
    Route::get('/parcours/{plugin}/jour/{day}',   [\App\Http\Controllers\Candidate\JourneyDashboardController::class, 'show'])->whereNumber('day')->name('journey.show');
    Route::post('/parcours/{plugin}/jour/{day}',  [\App\Http\Controllers\Candidate\JourneyDashboardController::class, 'complete'])->whereNumber('day')->name('journey.complete.day');

    // ─── Tip du jour mini-apps (Salle du Trésor) ─────────────────────────────
    // {plugin} = slug de la mini-app (praxizen, praxispeak, praxiflow…)
    // L'app doit être débloquée (gating RewardCatalog dans DailyTipController).
    Route::prefix('apps/{plugin}/tip')->name('daily.tip.')->middleware('throttle:30,1')->group(function () {
        Route::post('/seen',  [DailyTipController::class, 'seen'])->name('seen');
        Route::post('/apply', [DailyTipController::class, 'apply'])->name('apply');
    });

    // ─── RGPD — Droits des personnes (Art. 15, 17, 20) ────────────────────────
    Route::prefix('account/gdpr')->name('gdpr.')->group(function () {
        Route::get('/',          [GdprController::class, 'show'])->name('show');
        Route::get('/export',       [GdprController::class, 'export'])->name('export');
        Route::delete('/delete',    [GdprController::class, 'destroy'])->name('destroy');
        Route::delete('/delete-cv', [GdprController::class, 'destroyCv'])->name('destroy-cv');
    });

    // Édition du profil (mise à jour post-onboarding)
    Route::get('/profile/edit',   [OnboardingController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',        [OnboardingController::class, 'update'])->name('profile.update');
});

// ─── Croyances bloquantes (relance parcours journaliers) ────────────────────
Route::middleware(['auth'])->prefix('mon-parcours')->name('beliefs.')->group(function () {
    Route::get('/croyances',  [BlockingBeliefsController::class, 'show'])->name('show');
    Route::post('/croyances', [BlockingBeliefsController::class, 'store'])->name('store');
});

// ─── Billing / Stripe ────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('/plans',    [BillingController::class, 'plans'])->name('plans');
    Route::post('/checkout',[BillingController::class, 'checkout'])->name('checkout');
    Route::get('/manage',   [BillingController::class, 'manage'])->name('manage');
    Route::get('/portal',   [BillingController::class, 'portal'])->name('portal');
    Route::get('/success',  [BillingController::class, 'success'])->name('success');
    Route::post('/cancel',  [BillingController::class, 'cancel'])->name('cancel');
    Route::post('/resume',  [BillingController::class, 'resume'])->name('resume');
});

// Lien d'invitation public (rate-limité anti-énumération de tokens)
Route::get('/i/{token}', [\App\Http\Controllers\InvitationController::class, 'land'])
    ->middleware('throttle:30,1')
    ->name('invitation.land');

// Feedback 360° — réponse anonyme d'un évaluateur (sans compte, via lien tokenisé).
// SEC-M11 : Rate-limité — 40 req/min sur l'ensemble du groupe (déjà < 60 demandé).
// Les routes d'écriture (answer/complete) ont un throttle additionnel plus strict.
Route::middleware('throttle:40,1')->group(function () {
    Route::get('/360/{token}',          [\App\Http\Controllers\Evaluation360Controller::class, 'land'])->name('eval360.land');
    Route::post('/360/{token}/answer',  [\App\Http\Controllers\Evaluation360Controller::class, 'answer'])->middleware('throttle:60,1')->name('eval360.answer');
    Route::post('/360/{token}/complete',[\App\Http\Controllers\Evaluation360Controller::class, 'complete'])->middleware('throttle:60,1')->name('eval360.complete');
    Route::get('/360/{token}/merci',    [\App\Http\Controllers\Evaluation360Controller::class, 'thanks'])->name('eval360.thanks');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/profile_share.php';

// ─── Stripe Webhook (Cashier) ────────────────────────────────────────────────
// Pas de middleware auth (Stripe ne s'authentifie pas via session).
// CSRF exclu dans bootstrap/app.php (validateCsrfTokens except: ['stripe/webhook']).
// Laravel Cashier vérifie la signature de l'événement via STRIPE_WEBHOOK_SECRET.
// Guard : si cashier n'est pas encore installé (composer.lock ancien), la route
// est simplement absente plutôt que de crasher package:discover.
if (class_exists(\Laravel\Cashier\Http\Controllers\WebhookController::class)) {
    Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])
        ->name('cashier.webhook');
}
