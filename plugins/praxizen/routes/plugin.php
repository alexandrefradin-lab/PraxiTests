<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('plugin/praxizen')
    ->name('praxizen.')
    ->group(function () {
        // Routes spécifiques PraxiZen — extensible.
    });
