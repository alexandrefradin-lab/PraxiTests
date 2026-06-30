<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mirror_exercises', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('day_index')->unique();   // 1..30
            $table->string('bloc');                                // bloc thématique
            $table->string('title');
            $table->string('summary');                             // visible même verrouillé
            $table->longText('body');                              // contenu markdown
            $table->string('prompt');                              // question principale de réflexion
            $table->unsignedSmallInteger('duration_min')->default(15);
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'day_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mirror_exercises');
    }
};
