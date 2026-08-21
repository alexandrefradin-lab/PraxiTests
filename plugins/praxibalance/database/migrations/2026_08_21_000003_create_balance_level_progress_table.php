<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_level_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('level');              // 1..6
            $table->unsignedTinyInteger('best_score');         // % du meilleur passage
            $table->timestamp('completed_at')->nullable();     // premier passage validé
            $table->boolean('eclats_awarded')->default(false); // anti double-octroi
            $table->timestamps();

            $table->unique(['user_id', 'level']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_level_progress');
    }
};
