<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Garde Schema::hasColumn : ne jamais déduire le schéma prod des fichiers
        // de migration (écarts constatés le 2026-07-04 sur leads.deleted_at).
        if (! Schema::hasColumn('users', 'ui_theme')) {
            Schema::table('users', function (Blueprint $table) {
                // 'medieval' = univers Parchemin/Or (défaut) · 'corporate' = cabinet de conseil
                $table->string('ui_theme', 20)->default('medieval')->after('locale');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'ui_theme')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ui_theme');
            });
        }
    }
};
