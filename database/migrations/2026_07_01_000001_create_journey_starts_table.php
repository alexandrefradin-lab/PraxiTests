<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date de démarrage d'un parcours par utilisateur et par plugin.
 * Permet le déverrouillage TEMPOREL (1 jour = 1 date calendaire écoulée depuis
 * started_on), comme le modèle de référence PraxiVision. La progression réelle
 * (jours complétés, ressenti, notes) reste dans la table `journey_progress`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('journey_starts')) {
            return;
        }

        Schema::create('journey_starts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plugin_slug');
            $table->date('started_on');
            $table->timestamps();

            $table->unique(['user_id', 'plugin_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_starts');
    }
};
