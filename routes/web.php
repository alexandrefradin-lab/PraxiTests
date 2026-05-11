<?php

use App\Http\Controllers\Candidate\AttemptController;
use App\Http\Controllers\Candidate\OnboardingController;
use App\Http\Controllers\Candidate\ResultController;
use App\Http\Controllers\Candidate\TestController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

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
    Route::get('/results/{attempt}/pdf', [ResultController::class, 'pdf'])->name('results.pdf');
});

// Lien d'invitation public
Route::get('/i/{token}', [\App\Http\Controllers\InvitationController::class, 'land'])->name('invitation.land');

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
