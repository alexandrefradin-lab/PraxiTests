<?php

use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ConseillerDashboardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InsightsRetryController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TestEditorController;
use Illuminate\Support\Facades\Route;

// Dashboard + Leads + Campagnes : accessibles aux professionnels et aux admins
// NOTE : 'verified' retiré — aucun flux de vérification d'email n'est implémenté
// (route verification.notice inexistante → 500). À réintroduire avec le flux complet.
// '2fa' : si l'admin/pro a activé le 2FA, la session doit être confirmée (audit A-2).
Route::middleware(['auth', 'role:admin|professional', '2fa'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/conseiller', [ConseillerDashboardController::class, 'index'])->name('conseiller');
        Route::resource('leads', LeadController::class);
        Route::resource('campaigns', CampaignController::class);
        Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
    });

// Configuration sensible (tests, plugins, réglages) : réservée aux admins (SEC-13)
Route::middleware(['auth', 'role:admin', '2fa'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::resource('tests', TestEditorController::class);
        Route::put('tests/{test}/structure', [TestEditorController::class, 'saveStructure'])->name('tests.structure');
        Route::resource('plugins', PluginController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('plugins/{plugin}/activate',   [PluginController::class, 'activate'])->name('plugins.activate');
        Route::post('plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
        Route::get('settings', [SettingsController::class, 'show'])->name('settings');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');
        // Retry IA : relance la génération de synthèse pour une tentative en échec
        Route::post('attempts/{attempt}/retry-insights', [InsightsRetryController::class, 'retry'])->name('attempts.retry-insights');
        Route::get('attempts/failed-insights', [InsightsRetryController::class, 'index'])->name('attempts.failed-insights');
    });
