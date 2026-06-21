<?php

use App\Http\Controllers\Candidate\AttemptController;
use App\Http\Controllers\Candidate\GrimoireController;
use App\Http\Controllers\Candidate\JourneyController;
use App\Http\Controllers\Candidate\OnboardingController;
use App\Http\Controllers\Candidate\ResultController;
use App\Http\Controllers\Candidate\TestController;
use App\Http\Controllers\Candidate\TreasureController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\GdprController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cgu', [LegalController::class, 'cgu'])->name('cgu');

// NOTE : Le middleware 'subscribed' (EnsureSubscribed) est disponible mais
// non appliqué ici — accès libre en phase bêta. Pour activer le paywall,
// ajouter 'subscribed' au middleware group ci-dessous.
// NOTE : Le middleware 'verified' a été retiré car aucun flux de vérification
// d'email n'est implémenté (route verification.notice inexistante → 500).
// À réintroduire avec les routes/contrôleur/mail de vérification si besoin.
Route::middleware(['auth'])->group(function () {
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

    // La Salle du Trésor — apps offertes en récompense (déblocage par paliers d'Éclats)
    Route::get('/salle-du-tresor', [TreasureController::class, 'index'])->name('treasure.index');

    // Le Grimoire — relecture globale transversale de tous les tests
    Route::get('/grimoire',          [GrimoireController::class, 'show'])->name('grimoire.show');
    Route::get('/grimoire/status',   [GrimoireController::class, 'status'])->name('grimoire.status');
    Route::get('/grimoire/pdf',      [GrimoireController::class, 'pdf'])->name('grimoire.pdf');
    Route::post('/grimoire/refresh', [GrimoireController::class, 'refresh'])->name('grimoire.refresh');

    // Journey 60 jours
    Route::post('/journey/complete', [JourneyController::class, 'completeDay'])->name('journey.complete');
    Route::get('/journey/today',     [JourneyController::class, 'todayData'])->name('journey.today');

    // ─── RGPD — Droits des personnes (Art. 15, 17, 20) ────────────────────────
    Route::prefix('account/gdpr')->name('gdpr.')->group(function () {
        Route::get('/',          [GdprController::class, 'show'])->name('show');
        Route::get('/export',    [GdprController::class, 'export'])->name('export');
        Route::delete('/delete', [GdprController::class, 'destroy'])->name('destroy');
    });

    // Édition du profil (mise à jour post-onboarding)
    Route::get('/profile/edit',   [OnboardingController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',        [OnboardingController::class, 'update'])->name('profile.update');
});

// ─── Billing / Stripe ────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('/plans',    [BillingController::class, 'plans'])->name('plans');
    Route::post('/checkout',[BillingController::class, 'checkout'])->name('checkout');
    Route::get('/manage',   [BillingController::class, 'manage'])->name('manage');
    Route::get('/portal',   [BillingController::class, 'portal'])->name('portal');
    Route::get('/success',  [BillingController::class, 'success'])->name('success');
    Route::post('/cancel',  [BillingController::class, 'cancel'])->name('cancel');
    Route::post('/resume',  [BillingController::class, 'resume'])->name('resume');
});

// Lien d'invitation public
Route::get('/i/{token}', [\App\Http\Controllers\InvitationController::class, 'land'])->name('invitation.land');

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/profile_share.php';
