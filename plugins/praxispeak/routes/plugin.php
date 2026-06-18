<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('plugin/praxispeak')
    ->name('praxispeak.')
    ->group(function () {
        // Routes spécifiques PraxiSpeak — extensible.
    });
