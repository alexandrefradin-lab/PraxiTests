<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace les relances envoyées aux utilisateurs qui n'ont pas effectué
 * leur action du jour dans les mini-apps à parcours journalier.
 * Permet d'éviter l'envoi multiple le même jour et de conserver
 * l'historique des réponses au questionnaire sur les croyances.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_nudge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plugin', 40);      // 'praxilead', 'praxizenith', ...
            $table->unsignedSmallInteger('day');
            $table->date('nudged_on');          // une seule relance par jour/plugin
            $table->timestamps();

            $table->unique(['user_id', 'plugin', 'nudged_on']);
            $table->index('nudged_on');
        });

        Schema::create('journey_nudge_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plugin', 40);
            $table->unsignedSmallInteger('day');
            // Les 5 étapes du questionnaire
            $table->text('q1_obstacle')->nullable();         // ce qui a bloqué (texte libre)
            $table->string('q2_category', 40)->nullable();   // peur / fatigue / temps / croyance
            $table->unsignedTinyInteger('q3_score')->nullable(); // 0-10
            $table->text('q4_friend_advice')->nullable();    // que dirais-tu à un ami ?
            $table->text('q5_small_step')->nullable();       // quel micro-pas ?
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_nudge_responses');
        Schema::dropIfExists('journey_nudge_logs');
    }
};
