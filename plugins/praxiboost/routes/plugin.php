<?php

use Illuminate\Support\Facades\Route;
use Praxis\Plugins\PraxiBoost\Http\ExerciseController;

Route::middleware(['web', 'auth'])
    ->prefix('exercices')
    ->name('praxiboost.')
    ->group(function () {
        Route::get('/', [ExerciseController::class, 'index'])->name('index');
        Route::get('/{slug}', [ExerciseController::class, 'show'])->name('show');
        Route::post('/{slug}/done', [ExerciseController::class, 'complete'])->name('complete');
    });
