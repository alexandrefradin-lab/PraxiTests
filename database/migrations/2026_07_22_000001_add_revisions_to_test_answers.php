<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Compteur de changements d'avis par réponse.
 *
 * Alimente l'easter egg « Le Doute » : réviser plusieurs fois la même réponse
 * pendant une épreuve. Seul un changement RÉEL de valeur incrémente — les
 * ré-enregistrements à valeur identique (autosave) ne comptent pas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_answers', function (Blueprint $table) {
            $table->unsignedSmallInteger('revisions')->default(0)->after('time_spent_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('test_answers', function (Blueprint $table) {
            $table->dropColumn('revisions');
        });
    }
};
