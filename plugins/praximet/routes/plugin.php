<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('plugin/praximet')
    ->name('praximet.')
    ->group(function () {
        // Routes spécifiques PraxiMet (admin du plugin) — extensible.
    });
