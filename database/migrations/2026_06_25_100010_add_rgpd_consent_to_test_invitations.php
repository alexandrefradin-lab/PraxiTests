<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEC-M12 — Colonnes de consentement RGPD sur test_invitations.
 *
 * consent_share_professional : le candidat autorise le partage de ses résultats
 *   avec le professionnel/recruteur qui a déclenché l'invitation.
 * consent_given_at : horodatage du consentement (preuve auditée).
 *
 * Les deux colonnes sont ajoutées uniquement si elles n'existent pas encore
 * (idempotence en cas de ré-exécution).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('test_invitations')) {
            return;
        }

        Schema::table('test_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('test_invitations', 'consent_share_professional')) {
                $table->boolean('consent_share_professional')->default(false)->after('status');
            }

            if (!Schema::hasColumn('test_invitations', 'consent_given_at')) {
                $table->timestamp('consent_given_at')->nullable()->after('consent_share_professional');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('test_invitations')) {
            return;
        }

        Schema::table('test_invitations', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('test_invitations', 'consent_given_at')) {
                $columns[] = 'consent_given_at';
            }

            if (Schema::hasColumn('test_invitations', 'consent_share_professional')) {
                $columns[] = 'consent_share_professional';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
