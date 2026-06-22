<?php

use Illuminate\Support\Facades\Route;
use Praxis\Core\Library\Http\LibraryController;

/*
| PraxiLink — Bibliothèque d'exercices (mises en situation). Plus de test.
*/
Route::middleware(['web', 'auth'])
    ->prefix('coffre/praxilink')
    ->name('praxilink.')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->defaults('plugin', 'praxilink')->name('index');
        Route::get('/{exercise}', [LibraryController::class, 'show'])
            ->defaults('plugin', 'praxilink')->name('show');
        Route::post('/{exercise}/done', [LibraryController::class, 'complete'])
            ->defaults('plugin', 'praxilink')->name('complete');
    });
