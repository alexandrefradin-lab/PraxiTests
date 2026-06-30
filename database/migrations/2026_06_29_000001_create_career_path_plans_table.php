<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plan d'action IA par (profil × piste métier).
 *
 * Stocké une seule fois et réutilisé (pas de regénération sauf demande explicite).
 * Clé métier : profile_id + career_path_id (unique).
 *
 * plan_json = { premier_pas, etapes[], ressources[], conseil }
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_path_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')
                ->constrained('profiles')
                ->cascadeOnDelete();
            $table->foreignId('career_path_id')
                ->constrained('career_paths')
                ->cascadeOnDelete();
            $table->json('plan_json')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'career_path_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_path_plans');
    }
};
