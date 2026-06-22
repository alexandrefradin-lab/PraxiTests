<?php

use Illuminate\Support\Facades\Route;
use Praxis\Core\Library\Http\LibraryController;

/*
| PraxiZen — Bibliothèque d'exercices (Salle du Trésor). Plus de test.
*/
Route::middleware(['web', 'auth'])
    ->prefix('coffre/praxizen')
    ->name('praxizen.')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->defaults('plugin', 'praxizen')->name('index');
        Route::get('/{exercise}', [LibraryController::class, 'show'])
            ->defaults('plugin', 'praxizen')->name('show');
        Route::post('/{exercise}/done', [LibraryController::class, 'complete'])
            ->defaults('plugin', 'praxizen')->name('complete');
    });
