<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('plugin/praxiself')
    ->name('praxiself.')
    ->group(function () {
        // Routes spécifiques PraxiSelf — extensible.
    });
