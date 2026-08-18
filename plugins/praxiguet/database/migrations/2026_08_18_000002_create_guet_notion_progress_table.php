<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guet_notion_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identifiant de la notion (src/Data/Notions.php), pas de la phrase :
            // c'est la notion qui s'ancre, la formulation change à chaque passage.
            $table->string('notion_id', 16);

            // Boîte de Leitner 0..4. Une erreur fait redescendre d'un cran.
            $table->unsignedTinyInteger('box')->default(0);

            // Session à partir de laquelle la notion redevient due.
            $table->unsignedInteger('due_session')->default(0);

            // Dernière formulation servie, pour ne jamais répéter la même phrase.
            $table->unsignedTinyInteger('variant_index')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'notion_id']);
            $table->index(['user_id', 'due_session']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guet_notion_progress');
    }
};
