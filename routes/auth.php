<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

// ─── Défi 2FA post-login ─────────────────────────────────────────────────────
// Pas de middleware 'guest' : l'utilisateur n'est pas encore authentifié au sens
// Laravel (on stocke juste two_factor_user_id en session). Rate-limité anti-brute-force.
Route::get('/two-factor-challenge',  [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])->middleware('throttle:5,1');

// ─── Gestion du 2FA depuis le compte (auth requis) ────────────────────────────
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/two-factor',                 [TwoFactorController::class, 'show'])->name('two-factor');
    Route::post('/two-factor/enable',         [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/disable',        [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateCodes'])->name('two-factor.recovery-codes');
});

// ─── Authentification standard ───────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,60');

    // Reset password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:3,10');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
