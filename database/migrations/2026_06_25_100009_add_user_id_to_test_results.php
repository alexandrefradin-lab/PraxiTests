<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M4 — Colonne user_id nullable sur test_results.
 *
 * Permet d'accéder directement aux résultats d'un utilisateur sans passer
 * par la jointure test_results → test_attempts → user_id.
 * La colonne est nullable pour les passages anonymes (évaluateurs 360°, etc.).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('test_results')) {
            return;
        }

        if (!Schema::hasColumn('test_results', 'user_id')) {
            Schema::table('test_results', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();

                $table->index('user_id', 'test_results_user_id_index');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('test_results')) {
            return;
        }

        if (Schema::hasColumn('test_results', 'user_id')) {
            Schema::table('test_results', function (Blueprint $table) {
                $table->dropIndex('test_results_user_id_index');
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
