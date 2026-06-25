<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute un flag ai_failed sur test_results pour distinguer :
 *  - ai_synthesis NULL    → synthèse pas encore générée (job en attente)
 *  - ai_failed    = true  → job a échoué, texte de repli écrit dans ai_synthesis
 *  - ai_failed    = false → synthèse réelle (succès)
 *
 * Permet un retry admin ciblé et un affichage dégradé côté candidat.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->boolean('ai_failed')->default(false)->after('ai_generated_at')
                ->comment('true si la génération IA a échoué (texte de repli dans ai_synthesis)');

            $table->text('ai_error')->nullable()->after('ai_failed')
                ->comment('Message d\'erreur technique pour le diagnostic admin');
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn(['ai_failed', 'ai_error']);
        });
    }
};
