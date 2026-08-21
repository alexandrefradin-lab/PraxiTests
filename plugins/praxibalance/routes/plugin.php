<?php

use Illuminate\Support\Facades\Route;
use Praxis\Plugins\PraxiBalance\Http\BalanceController;

Route::middleware(['web', 'auth'])
    ->prefix('la-balance')
    ->name('praxibalance.')
    ->group(function () {
        Route::get('/', [BalanceController::class, 'index'])->name('index');
        Route::post('/session', [BalanceController::class, 'store'])->name('session.store');
    });
