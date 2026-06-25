<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MET-M5 — Colonne idempotency_key sur xp_events.
 *
 * Permet d'éviter les doublons d'attribution d'Éclats en cas de
 * double-soumission ou de retry (clé unique par événement métier).
 * Nullable pour compatibilité avec les événements déjà enregistrés.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('xp_events')) {
            return;
        }

        if (!Schema::hasColumn('xp_events', 'idempotency_key')) {
            Schema::table('xp_events', function (Blueprint $table) {
                $table->string('idempotency_key', 64)->nullable()->unique()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('xp_events')) {
            return;
        }

        if (Schema::hasColumn('xp_events', 'idempotency_key')) {
            Schema::table('xp_events', function (Blueprint $table) {
                $table->dropUnique(['idempotency_key']);
                $table->dropColumn('idempotency_key');
            });
        }
    }
};
