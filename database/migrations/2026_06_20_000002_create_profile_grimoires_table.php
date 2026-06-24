<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le Grimoire global — relecture transversale de TOUS les tests d'un candidat.
 *
 * 1 ligne par utilisateur (vue "courante" du profil, mise à jour en place).
 * Vient PAR-DESSUS les synthèses par test (test_results), sans les remplacer.
 *
 *  - synthesis       : texte de synthèse croisée (« en croisant tes tests… »)
 *  - voies           : 15 pistes de métiers consolidées, avec appui_tests
 *  - tests_included  : tentatives prises en compte (traçabilité affichée au candidat)
 *  - tests_signature : empreinte des tentatives → évite les régénérations à vide
 *  - ai_*            : traçabilité IA (cohérent avec test_results)
 *  - status          : pending | ready | failed
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('profile_grimoires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->longText('synthesis')->nullable();
            $table->json('voies')->nullable();
            $table->json('tests_included')->nullable();
            $table->string('tests_signature')->nullable();

            $table->string('ai_driver')->nullable();
            $table->string('ai_model')->nullable();
            $table->unsignedInteger('ai_tokens_used')->nullable();
            $table->json('ai_metadata')->nullable();

            $table->string('status')->default('pending'); // pending | ready | failed
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_grimoires');
    }
};
