<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_exercise_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plugin_slug');                 // ex. praxispeak, praxizen
            $table->string('exercise_id');                 // id de l'exercice dans le plugin
            $table->timestamp('completed_at')->nullable();  // « marqué comme fait »
            $table->tinyInteger('felt_score')->nullable();  // ressenti 1-5
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plugin_slug', 'exercise_id'], 'lib_progress_unique');
            $table->index(['user_id', 'plugin_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_exercise_progress');
    }
};
