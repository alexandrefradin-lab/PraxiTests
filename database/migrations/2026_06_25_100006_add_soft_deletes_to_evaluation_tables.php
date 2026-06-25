<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M5 — SoftDeletes sur evaluation_panels et evaluation_invitations.
 *
 * Permet de fermer/archiver un panel ou d'annuler une invitation sans perte
 * des données d'évaluation déjà recueillies.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('evaluation_panels') && !Schema::hasColumn('evaluation_panels', 'deleted_at')) {
            Schema::table('evaluation_panels', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('evaluation_invitations') && !Schema::hasColumn('evaluation_invitations', 'deleted_at')) {
            Schema::table('evaluation_invitations', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('evaluation_panels') && Schema::hasColumn('evaluation_panels', 'deleted_at')) {
            Schema::table('evaluation_panels', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('evaluation_invitations') && Schema::hasColumn('evaluation_invitations', 'deleted_at')) {
            Schema::table('evaluation_invitations', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
