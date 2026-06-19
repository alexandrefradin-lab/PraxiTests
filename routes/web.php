<?php

use App\Http\Controllers\Candidate\AttemptController;
use App\Http\Controllers\Candidate\JourneyController;
use App\Http\Controllers\Candidate\OnboardingController;
use App\Http\Controllers\Candidate\ResultController;
use App\Http\Controllers\Candidate\TestController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cgu', [LegalController::class, 'cgu'])->name('cgu');

// NOTE : Le middleware 'subscribed' (EnsureSubscribed) est disponible mais
// non appliqué ici — accès libre en phase bêta. Pour activer le paywall,
// ajouter 'subscribed' au middleware group ci-dessous.
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

    // Journey 60 jours
    Route::post('/journey/complete', [JourneyController::class, 'completeDay'])->name('journey.complete');
    Route::get('/journey/today',     [JourneyController::class, 'todayData'])->name('journey.today');
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

// Lien d'invitation public
Route::get('/i/{token}', [\App\Http\Controllers\InvitationController::class, 'land'])->name('invitation.land');

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
