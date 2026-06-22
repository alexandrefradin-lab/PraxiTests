<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index manquants relevés à l'audit du 2026-06-21 (T8).
 *
 * Colonnes interrogées en filtre/jointure sans index :
 *  - test_attempts.rater_relation (agrégateur 360° par relation, dans un panel) → index composite (panel_id, rater_relation)
 *  - test_invitations.professional_account_id (belongsTo sans index)
 *  - email_campaigns.professional_account_id / email_sequences.professional_account_id (listing par compte pro)
 *
 * Chaque ajout est protégé par try/catch : si l'index existe déjà sur un
 * environnement donné, la migration n'échoue pas (idempotence).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->safe('test_attempts', function (Blueprint $t) {
            $t->index(['panel_id', 'rater_relation'], 'test_attempts_panel_relation_idx');
        });

        $this->safe('test_invitations', function (Blueprint $t) {
            $t->index('professional_account_id', 'test_invitations_pro_account_idx');
        });

        $this->safe('email_campaigns', function (Blueprint $t) {
            $t->index('professional_account_id', 'email_campaigns_pro_account_idx');
        });

        $this->safe('email_sequences', function (Blueprint $t) {
            $t->index('professional_account_id', 'email_sequences_pro_account_idx');
        });
    }

    public function down(): void
    {
        $this->safe('test_attempts', fn (Blueprint $t) => $t->dropIndex('test_attempts_panel_relation_idx'));
        $this->safe('test_invitations', fn (Blueprint $t) => $t->dropIndex('test_invitations_pro_account_idx'));
        $this->safe('email_campaigns', fn (Blueprint $t) => $t->dropIndex('email_campaigns_pro_account_idx'));
        $this->safe('email_sequences', fn (Blueprint $t) => $t->dropIndex('email_sequences_pro_account_idx'));
    }

    /** Applique une modification de schéma en ignorant l'erreur si l'index existe/n'existe pas déjà. */
    private function safe(string $table, \Closure $cb): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        try {
            Schema::table($table, $cb);
        } catch (\Throwable $e) {
            // index déjà présent/absent → on ignore (idempotence cross-env)
        }
    }
};
