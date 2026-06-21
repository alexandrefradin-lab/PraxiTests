<?php

use App\Http\Controllers\ProfileShareController;
use Illuminate\Support\Facades\Route;

// ── Authentifié ──────────────────────────────────────────────────────────────
// NOTE : 'verified' retiré (pas de flux de vérification d'email → 500).
Route::middleware(['auth'])->group(function () {
    Route::post('/profile/share',   [ProfileShareController::class, 'store']);
    Route::delete('/profile/share', [ProfileShareController::class, 'destroy']);
});

// ── Public (aucune authentification requise) ─────────────────────────────────
// Rate limiting anti-énumération de tokens (et anti-incrément de vues abusif).
Route::get('/p/{token}', [ProfileShareController::class, 'show'])
     ->middleware('throttle:30,1')
     ->name('profile.shared')
     ->where('token', '[A-Za-z0-9]{48}');
