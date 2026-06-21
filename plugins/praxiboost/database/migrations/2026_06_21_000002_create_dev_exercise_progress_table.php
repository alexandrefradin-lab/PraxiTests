<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dev_exercise_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('exercise_slug');
            $table->timestamp('unlocked_at')->nullable();   // franchissement du palier
            $table->timestamp('completed_at')->nullable();  // « marqué comme fait »
            $table->tinyInteger('felt_score')->nullable();  // ressenti 1-5
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'exercise_slug']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dev_exercise_progress');
    }
};
