<?php

use Illuminate\Support\Facades\Route;
use Praxis\Plugins\PraxiMiroir\Http\MirrorController;

Route::middleware(['web', 'auth'])
    ->prefix('miroir')
    ->name('praximiroir.')
    ->group(function () {
        Route::get('/', [MirrorController::class, 'index'])->name('index');
        Route::get('/jour/{day}', [MirrorController::class, 'show'])
            ->whereNumber('day')->name('show');
        Route::post('/jour/{day}/done', [MirrorController::class, 'complete'])
            ->whereNumber('day')->name('complete');
    });
