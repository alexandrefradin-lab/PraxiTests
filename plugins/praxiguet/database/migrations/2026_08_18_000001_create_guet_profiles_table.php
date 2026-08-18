<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guet_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Compteur de sessions : c'est l'horloge de la répétition espacée.
            // Une notion est due quand due_session <= sessions_count.
            $table->unsignedInteger('sessions_count')->default(0);
            $table->unsignedInteger('points')->default(0);

            // Temps de réaction moyen des séries chronométrées (indicateur de
            // jeu, jamais une mesure clinique).
            $table->unsignedSmallInteger('mean_rt_ms')->nullable();
            $table->unsignedInteger('rt_samples')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guet_profiles');
    }
};
