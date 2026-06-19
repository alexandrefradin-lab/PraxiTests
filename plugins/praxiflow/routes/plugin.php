<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('plugin/praxiflow')
    ->name('praxiflow.')
    ->group(function () {
        // Routes spécifiques PraxiFlow — extensible.
    });
