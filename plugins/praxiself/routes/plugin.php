<?php

use Illuminate\Support\Facades\Route;
use Praxis\Core\Library\Http\LibraryController;

/*
| PraxiSelf — Bibliothèque d'exercices (Salle du Trésor). Plus de test.
*/
Route::middleware(['web', 'auth'])
    ->prefix('coffre/praxiself')
    ->name('praxiself.')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->defaults('plugin', 'praxiself')->name('index');
        Route::get('/{exercise}', [LibraryController::class, 'show'])
            ->defaults('plugin', 'praxiself')->name('show');
        Route::post('/{exercise}/done', [LibraryController::class, 'complete'])
            ->defaults('plugin', 'praxiself')->name('complete');
    });
