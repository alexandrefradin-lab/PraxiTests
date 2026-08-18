<?php

use Illuminate\Support\Facades\Route;
use Praxis\Plugins\PraxiGuet\Http\GuetController;

Route::middleware(['web', 'auth'])
    ->prefix('tour-de-guet')
    ->name('praxiguet.')
    ->group(function () {
        Route::get('/', [GuetController::class, 'index'])->name('index');
        Route::post('/session', [GuetController::class, 'store'])->name('session.store');
    });
