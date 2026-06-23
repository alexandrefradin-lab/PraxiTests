<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('focus_exercise_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_index');       // 1..60
            $table->timestamp('completed_at')->nullable();   // « exercice appliqué »
            $table->tinyInteger('felt_score')->nullable();   // ressenti 1-5
            $table->text('notes')->nullable();
            $table->boolean('eclats_awarded')->default(false); // anti double-octroi
            $table->timestamps();

            $table->unique(['user_id', 'day_index']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focus_exercise_progress');
    }
};
