<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('professional_account_id')->nullable();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'sent', 'opened', 'started', 'completed', 'expired'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'test_id']);
            $table->index(['status', 'expires_at']);
        });

        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('test_invitations')->nullOnDelete();
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->unsignedInteger('current_section')->default(0);
            $table->unsignedInteger('current_question')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->json('progress')->nullable(); // section -> %
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'test_id']);
            $table->index(['status', 'completed_at']);
        });

        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('test_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('test_questions')->cascadeOnDelete();
            $table->json('value');
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
        });

        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->unique()->constrained('test_attempts')->cascadeOnDelete();
            $table->json('scoring');                 // résultats du scoring engine
            $table->longText('ai_synthesis')->nullable();
            $table->json('suggested_jobs')->nullable(); // [ { title, why, fit_score, sector, ...}, ... ]
            $table->json('strengths')->nullable();
            $table->json('growth_areas')->nullable();
            $table->json('insights_unlocked')->nullable();
            $table->string('ai_driver')->nullable();
            $table->unsignedInteger('ai_tokens_used')->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('test_answers');
        Schema::dropIfExists('test_attempts');
        Schema::dropIfExists('test_invitations');
    }
};
