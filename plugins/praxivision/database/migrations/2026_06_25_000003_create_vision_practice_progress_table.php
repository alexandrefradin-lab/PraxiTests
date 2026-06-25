<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vision_practice_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('day_index');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('felt_score')->nullable();  // 1..5
            $table->text('notes')->nullable();
            $table->boolean('eclats_awarded')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'day_index']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_practice_progress');
    }
};
