<?php

use Illuminate\Support\Facades\Route;
use Praxis\Core\Library\Http\LibraryController;

/*
| PraxiFlow — Bibliothèque d'exercices (Salle du Trésor). Plus de test.
*/
Route::middleware(['web', 'auth'])
    ->prefix('coffre/praxiflow')
    ->name('praxiflow.')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->defaults('plugin', 'praxiflow')->name('index');
        Route::get('/{exercise}', [LibraryController::class, 'show'])
            ->defaults('plugin', 'praxiflow')->name('show');
        Route::post('/{exercise}/done', [LibraryController::class, 'complete'])
            ->defaults('plugin', 'praxiflow')->name('complete');
    });
