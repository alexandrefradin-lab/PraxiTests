<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conversation avec l'Oracle — chat IA d'orientation (widget flottant).
 *
 * Une ligne par message (user | assistant). L'historique est rejoué dans le
 * prompt à chaque tour pour donner de la mémoire conversationnelle. Le contexte
 * (profil, tests, Grimoire) n'est PAS stocké ici : il est reconstruit à la volée
 * à chaque appel pour rester frais.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('oracle_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant'])->index();
            $table->text('content');
            $table->unsignedInteger('tokens')->nullable();   // coût cumulé du tour (assistant)
            $table->timestamps();

            $table->index(['user_id', 'id']);   // récupération chronologique par candidat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oracle_messages');
    }
};
