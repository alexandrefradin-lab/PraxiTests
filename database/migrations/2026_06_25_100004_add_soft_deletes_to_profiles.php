<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M2 — SoftDeletes sur la table profiles.
 *
 * Permet la suppression logique des profils (RGPD : anonymisation sans perte
 * des données agrégées liées aux tests et résultats).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('profiles')) {
            return;
        }

        if (!Schema::hasColumn('profiles', 'deleted_at')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('profiles')) {
            return;
        }

        if (Schema::hasColumn('profiles', 'deleted_at')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
