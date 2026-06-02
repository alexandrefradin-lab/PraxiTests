<?php

use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TestEditorController;
use Illuminate\Support\Facades\Route;

// Dashboard + Leads : accessibles aux professionnels
Route::middleware(['auth', 'verified', 'role:admin|professional'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('leads', LeadController::class);
        Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
    });

// Admin uniquement
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::resource('tests', TestEditorController::class);
        Route::put('tests/{test}/structure', [TestEditorController::class, 'saveStructure'])->name('tests.structure');
        Route::resource('plugins', PluginController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('plugins/{plugin}/activate',   [PluginC