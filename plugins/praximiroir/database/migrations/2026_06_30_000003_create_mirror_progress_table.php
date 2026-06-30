<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mirror_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_index');
            $table->timestamp('completed_at')->nullable();
            $table->text('reflection')->nullable();     // réponse libre de l'utilisateur
            $table->tinyInteger('felt_score')->nullable(); // ressenti 1-5
            $table->boolean('eclats_awarded')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'day_index']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mirror_progress');
    }
};
