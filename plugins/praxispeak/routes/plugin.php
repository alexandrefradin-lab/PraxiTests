<?php

use Illuminate\Support\Facades\Route;
use Praxis\Core\Library\Http\LibraryController;

/*
| PraxiSpeak — Bibliothèque d'exercices (Salle du Trésor).
| Plus de test : on entre directement sur la bibliothèque. Le slug du plugin
| est figé en défaut de route pour que `reward.entry_route` pointe sur une
| route nommée sans argument.
*/
Route::middleware(['web', 'auth'])
    ->prefix('coffre/praxispeak')
    ->name('praxispeak.')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->defaults('plugin', 'praxispeak')->name('index');
        Route::get('/{exercise}', [LibraryController::class, 'show'])
            ->defaults('plugin', 'praxispeak')->name('show');
        Route::post('/{exercise}/done', [LibraryController::class, 'complete'])
            ->defaults('plugin', 'praxispeak')->name('complete');
    });
