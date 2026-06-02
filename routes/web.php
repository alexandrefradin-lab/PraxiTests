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

    // Mise à jour profil après onboarding
    Route::get('/profile/edit', [OnboardingController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [OnboardingController::class, 'update'])->name('profile.update');

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
    Route::get('/results/{attempt}/pdf', [ResultController::class, 'pdf'])->name('re