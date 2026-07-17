<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ConseillerDashboardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InsightsRetryController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TestEditorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;

// Dashboard + Leads + Campagnes + Invitations : accessibles aux professionnels et aux admins.
// SEC-M7 : 'verified' ajouté — les admins/pros s'inscrivent par flux standard
// (pas par token d'invitation), donc la vérification d'email s'applique.
// NB : ne PAS ajouter 'verified' aux routes candidats (token d'invitation).
// '2fa' : si l'admin/pro a activé le 2FA, la session doit être confirmée (audit A-2).
// 'subscribed' : paywall pro — inactif tant que PRAXIQUEST_BILLING_ENFORCED=false
// (phase bêta) ; les admins restent exemptés dans EnsureSubscribed.
Route::middleware(['auth', 'verified', 'role:admin|professional', '2fa', 'subscribed'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/conseiller', [ConseillerDashboardController::class, 'index'])->name('conseiller');

        // Exports CSV : déclarés AVANT les resources pour ne pas matcher {lead}
        Route::get('leads/export', [LeadController::class, 'export'])->name('leads.export');
        Route::get('invitations/export', [InvitationController::class, 'export'])->name('invitations.export');

        Route::resource('leads', LeadController::class);
        Route::post('leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');

        Route::resource('campaigns', CampaignController::class);
        Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
        Route::post('campaigns/{id}/restore', [CampaignController::class, 'restore'])->name('campaigns.restore');

        // ── Invitations candidat (tunnel B2B) : suivi + création + relance ────
        Route::get('invitations',         [InvitationController::class, 'index'])->name('invitations.index');
        Route::get('invitations/create',  [InvitationController::class, 'create'])->name('invitations.create');
        Route::post('invitations',        [InvitationController::class, 'store'])->name('invitations.store');
        Route::post('invitations/{invitation}/resend', [InvitationController::class, 'resend'])->name('invitations.resend');
        Route::delete('invitations/{invitation}',      [InvitationController::class, 'destroy'])->name('invitations.destroy');
        Route::post('invitations/{id}/restore',        [InvitationController::class, 'restore'])->name('invitations.restore');
    });

// Configuration sensible (tests, plugins, réglages, utilisateurs) : réservée aux admins (SEC-13)
Route::middleware(['auth', 'verified', 'role:admin', '2fa'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::resource('tests', TestEditorController::class);
        Route::put('tests/{test}/structure', [TestEditorController::class, 'saveStructure'])->name('tests.structure');
        Route::post('tests/{id}/restore', [TestEditorController::class, 'restore'])->name('tests.restore');

        Route::resource('plugins', PluginController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('plugins/{plugin}/activate',   [PluginController::class, 'activate'])->name('plugins.activate');
        Route::post('plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');

        Route::get('settings', [SettingsController::class, 'show'])->name('settings');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        // Test de connexion IA : valide clé + modèle du fournisseur sélectionné
        Route::post('settings/test-connection', [SettingsController::class, 'testConnection'])->name('settings.test-connection');

        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');
        Route::get('subscriptions/export', [SubscriptionController::class, 'export'])->name('subscriptions.export');

        // ── Gestion des utilisateurs ───────────────────────────────────────────
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::put('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::post('users/{user}/resend-verification', [UserController::class, 'resendVerification'])->name('users.resend-verification');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

        // ── Journal d'audit (lecture seule) ────────────────────────────────────
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Retry IA : relance la génération de synthèse pour une tentative en échec
        Route::post('attempts/{attempt}/retry-insights', [InsightsRetryController::class, 'retry'])->name('attempts.retry-insights');
        Route::post('attempts/retry-all-insights', [InsightsRetryController::class, 'retryAll'])->name('attempts.retry-all-insights');
        Route::post('attempts/retry-zombie-insights', [InsightsRetryController::class, 'retryZombies'])->name('attempts.retry-zombie-insights');
        Route::get('attempts/failed-insights', [InsightsRetryController::class, 'index'])->name('attempts.failed-insights');
    });
