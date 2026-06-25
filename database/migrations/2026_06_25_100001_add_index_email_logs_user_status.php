<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M4 — Index composite (user_id, status) sur email_logs.
 *
 * Requis pour filtrer efficacement les logs par utilisateur + état
 * (ex. : "tous les mails envoyés à cet utilisateur").
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('email_logs')) {
            Log::warning('Migration skipped: table email_logs does not exist.');
            return;
        }

        try {
            Schema::table('email_logs', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'email_logs_user_id_status_index');
            });
        } catch (\Throwable $e) {
            Log::warning('Migration email_logs index skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        try {
            Schema::table('email_logs', function (Blueprint $table) {
                $table->dropIndex('email_logs_user_id_status_index');
            });
        } catch (\Throwable $e) {
            Log::warning('Migration email_logs index drop skipped: ' . $e->getMessage());
        }
    }
};
