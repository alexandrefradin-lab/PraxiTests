<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M5 — Index sur evaluation_panels.self_attempt_id.
 *
 * Permet de retrouver rapidement un panel depuis l'auto-évaluation du sujet.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('evaluation_panels')) {
            Log::warning('Migration skipped: table evaluation_panels does not exist.');
            return;
        }

        try {
            Schema::table('evaluation_panels', function (Blueprint $table) {
                $table->index('self_attempt_id', 'evaluation_panels_self_attempt_id_index');
            });
        } catch (\Throwable $e) {
            Log::warning('Migration evaluation_panels self_attempt_id index skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('evaluation_panels')) {
            return;
        }

        try {
            Schema::table('evaluation_panels', function (Blueprint $table) {
                $table->dropIndex('evaluation_panels_self_attempt_id_index');
            });
        } catch (\Throwable $e) {
            Log::warning('Migration evaluation_panels index drop skipped: ' . $e->getMessage());
        }
    }
};
