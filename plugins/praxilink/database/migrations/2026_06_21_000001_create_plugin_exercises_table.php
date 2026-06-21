<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crée la table des exercices de plugin (utilisée par le seeder PraxiLink).
     * Table partagée : indexée par `plugin_slug` pour pouvoir héberger
     * les exercices de plusieurs plugins (mini-apps).
     */
    public function up(): void
    {
        if (Schema::hasTable('plugin_exercises')) {
            return;
        }

        Schema::create('plugin_exercises', function (Blueprint $table) {
            $table->id();

            // Slug du plugin propriétaire (namespace logique)
            $table->string('plugin_slug')->index();

            // Identifiant d'exercice interne au plugin (ex. 'ea-01')
            $table->string('exercise_id');

            // Identifiant global unique : '<plugin_slug>:<exercise_id>' — clé d'upsert
            $table->string('plugin_exercise_id')->unique();

            $table->string('title');
            $table->string('category')->nullable();

            // Durée indicative et difficulté (1-5)
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedTinyInteger('difficulty')->nullable();

            // Fondement scientifique (texte libre)
            $table->text('scientific_basis')->nullable();

            // Dimension(s) de scoring : nom simple ou liste séparée par des virgules
            $table->string('scoring_dimension')->nullable();

            // Poids de l'exercice dans le score global
            $table->decimal('scoring_weight', 5, 2)->default(1.00);

            // Instructions de l'exercice (JSON encodé par le seeder)
            $table->json('instructions')->nullable();

            $table->timestamps();

            // Un même exercice ne peut exister qu'une fois par plugin
            $table->unique(['plugin_slug', 'exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_exercises');
    }
};
