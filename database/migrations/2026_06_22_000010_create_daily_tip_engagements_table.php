<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suivi de l'assiduité au « Tip du jour » de chaque mini-app.
 *
 * Une ligne par (utilisateur, plugin). On n'historise pas chaque jour : la
 * série (streak) se reconstitue à partir de last_engaged_on. Cela suffit pour
 * la mécanique « un tip sérieux chaque jour » + récompense de régularité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_tip_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plugin_slug');                  // ex. praxizen, praxiflow
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->unsignedInteger('total_applied')->default(0); // nb de jours « j'applique »
            $table->date('last_engaged_on')->nullable();    // dernier jour vu
            $table->date('last_applied_on')->nullable();    // dernier jour appliqué
            $table->string('last_tip_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plugin_slug'], 'daily_tip_unique');
            $table->index(['user_id', 'plugin_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_tip_engagements');
    }
};
