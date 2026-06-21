<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plugin_slug');
            $table->unsignedSmallInteger('day');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('duration_actual')->nullable();
            $table->tinyInteger('felt_score')->nullable(); // 1–5
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plugin_slug', 'day']);
            $table->index(['user_id', 'plugin_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_progress');
    }
};
