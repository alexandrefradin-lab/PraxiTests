<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * BDD-M1 — Clé étrangère user_id sur vision_practice_progress.
 *
 * La table a été créée avec un unsignedBigInteger('user_id') simple,
 * sans contrainte FK. Cette migration ajoute la contrainte vers users(id)
 * avec cascadeOnDelete pour garantir l'intégrité référentielle.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('vision_practice_progress')) {
            Log::warning('Migration skipped: table vision_practice_progress does not exist.');
            return;
        }

        try {
            Schema::table('vision_practice_progress', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            Log::warning('Migration vision_practice_progress FK skipped: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('vision_practice_progress')) {
            return;
        }

        try {
            Schema::table('vision_practice_progress', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
            Log::warning('Migration vision_practice_progress FK drop skipped: ' . $e->getMessage());
        }
    }
};
