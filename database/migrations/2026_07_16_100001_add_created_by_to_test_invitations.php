<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quota V1 : attribution des invitations à leur créateur.
 *
 * Le cloisonnement par professional_account_id est inopérant tant que le
 * multi-tenant n'est pas implémenté (aucun ProfessionalAccount créé) — le
 * quota mensuel de dossiers se compte donc par utilisateur créateur.
 * Nullable : les invitations historiques restent sans attribution.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Garde-fou (leçon du 2026-07-04) : ne jamais présumer du schéma réel.
        if (Schema::hasColumn('test_invitations', 'created_by')) {
            return;
        }

        Schema::table('test_invitations', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('professional_account_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('test_invitations', 'created_by')) {
            return;
        }

        Schema::table('test_invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
