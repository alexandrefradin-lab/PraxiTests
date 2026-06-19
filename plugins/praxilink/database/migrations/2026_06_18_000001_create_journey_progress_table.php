<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crée la table de suivi du parcours 60 jours PraxiLink.
     */
    public function up(): void
    {
        Schema::create('journey_progress', function (Blueprint $table) {
            $table->id();

            // Utilisateur propriétaire de la progression
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Slug du plugin — permet de réutiliser cette table pour d'autres plugins
            $table->string('plugin_slug');

            // Numéro du jour complété dans le parcours (1-60)
            $table->unsignedSmallInteger('day');

            // Timestamp de complétion (null = non complété)
            $table->timestamp('completed_at')->nullable();

            // Durée réelle de l'exercice en secondes (null = non renseigné)
            $table->unsignedSmallInteger('duration_actual')->nullable();

            // Ressenti subjectif après l'exercice (1-5, null = non renseigné)
            $table->tinyInteger('felt_score')->nullable();

            // Notes libres de l'utilisateur
            $table->text('notes')->nullable();

            $table->timestamps();

            // Contrainte d'unicité : un utilisateur ne peut avoir qu'une entrée par jour/plugin
            $table->unique(['user_id', 'plugin_slug', 'day']);

            // Index pour les requêtes fréquentes (streak, progression, dashboard)
            $table->index(['user_id', 'plugin_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journey_progress');
    }
};
