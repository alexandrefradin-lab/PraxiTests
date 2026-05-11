<?php

use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\TestEditorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin|professional'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('tests', TestEditorController::class);
        Route::resource('plugins', PluginController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('plugins/{plugin}/activate',   [PluginController::class, 'activate'])->name('plugins.activate');
        Route::post('plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
        Route::resource('leads', LeadController::class);
        Route::resource('campaigns', CampaignController::class);
        Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
    });
