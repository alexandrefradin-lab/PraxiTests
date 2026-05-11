<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->json('criteria');
            $table->unsignedSmallInteger('xp_reward')->default(0);
            $table->boolean('hidden')->default(false);
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at')->useCurrent();
            $table->json('context')->nullable();

            $table->unique(['user_id', 'badge_id']);
        });

        Schema::create('gamification_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('xp_total')->default(0);
            $table->unsignedTinyInteger('level')->default(1);
            $table->json('milestones_reached')->nullable();
            $table->json('insights_unlocked')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'test_id']);
        });

        Schema::create('xp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->integer('xp');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_events');
        Schema::dropIfExists('gamification_progress');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
