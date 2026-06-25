<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M6 — Index composite (panel_id, attempt_id) sur evaluation_invitations.
 *
 * Note : la table s'appelle evaluation_invitations (pas panel_invitations).
 * Requis pour retrouver efficacement l'invitation liée à un attempt dans un panel.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('evaluation_invitations')) {
            Log::warning('Migration skipped: table evaluation_invitations does not exist.');
            return;
        }

        try {
            Schema::table('evaluation_invitations', function (Blueprint $table) {
                $table->index(['panel_id', 'attempt_id'], 'evaluation_invitations_panel_attempt_index');
            });
        } catch (\Throwable $e) {
            Log::warning('Migration evaluation_invitations panel_attempt index skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('evaluation_invitations')) {
            return;
        }

        try {
            Schema::table('evaluation_invitations', function (Blueprint $table) {
                $table->dropIndex('evaluation_invitations_panel_attempt_index');
            });
        } catch (\Throwable $e) {
            Log::warning('Migration evaluation_invitations index drop skipped: ' . $e->getMessage());
        }
    }
};
