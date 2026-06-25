<?php

use Illuminate\Support\Facades\Route;
use Praxis\Plugins\PraxiVision\Http\PracticeController;

Route::middleware(['web', 'auth'])
    ->prefix('leadership')
    ->name('praxivision.')
    ->group(function () {
        Route::get('/', [PracticeController::class, 'index'])->name('index');
        Route::get('/jour/{day}', [PracticeController::class, 'show'])
            ->whereNumber('day')->name('show');
        Route::post('/jour/{day}/done', [PracticeController::class, 'complete'])
            ->whereNumber('day')->name('complete');
    });
