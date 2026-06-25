<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M3 — SoftDeletes sur test_questions et test_sections.
 *
 * Permet de "désactiver" des questions/sections sans les supprimer définitivement,
 * ce qui préserve l'intégrité des test_answers déjà enregistrées.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('test_questions') && !Schema::hasColumn('test_questions', 'deleted_at')) {
            Schema::table('test_questions', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('test_sections') && !Schema::hasColumn('test_sections', 'deleted_at')) {
            Schema::table('test_sections', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('test_questions') && Schema::hasColumn('test_questions', 'deleted_at')) {
            Schema::table('test_questions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('test_sections') && Schema::hasColumn('test_sections', 'deleted_at')) {
            Schema::table('test_sections', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
