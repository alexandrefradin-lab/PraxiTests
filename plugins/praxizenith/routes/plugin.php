<?php

use Illuminate\Support\Facades\Route;
use Praxis\Plugins\PraxiZenith\Http\FocusController;

Route::middleware(['web', 'auth'])
    ->prefix('sanctuaire')
    ->name('praxizenith.')
    ->group(function () {
        Route::get('/', [FocusController::class, 'index'])->name('index');
        Route::get('/jour/{day}', [FocusController::class, 'show'])
            ->whereNumber('day')->name('show');
        Route::post('/jour/{day}/done', [FocusController::class, 'complete'])
            ->whereNumber('day')->name('complete');
    });
