<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corbeille admin — soft deletes sur email_campaigns et test_invitations.
 *
 * Les campagnes étaient supprimées définitivement (DB::table()->delete()) et
 * les invitations n'avaient aucune suppression. Les deux passent en corbeille
 * restaurable depuis l'interface admin.
 *
 * Idempotent (hasColumn) comme les autres migrations soft-delete du projet.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('email_campaigns') && !Schema::hasColumn('email_campaigns', 'deleted_at')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('test_invitations') && !Schema::hasColumn('test_invitations', 'deleted_at')) {
            Schema::table('test_invitations', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('email_campaigns') && Schema::hasColumn('email_campaigns', 'deleted_at')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }

        if (Schema::hasTable('test_invitations') && Schema::hasColumn('test_invitations', 'deleted_at')) {
            Schema::table('test_invitations', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }
};
