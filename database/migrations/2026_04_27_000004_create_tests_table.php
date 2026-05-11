<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('questionnaire'); // questionnaire, situational, projective, etc.
            $table->string('scoring_engine')->default('default'); // ref to scoring engine
            $table->json('scoring_config')->nullable();
            $table->json('gamification')->nullable();   // overrides config par défaut
            $table->json('neuromarketing')->nullable();
            $table->unsignedSmallInteger('estimated_minutes')->default(15);
            $table->boolean('published')->default(false);
            $table->boolean('public')->default(false);  // accessible sans invitation
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['published', 'public']);
        });

        Schema::create('test_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('narrative_intro')->nullable();
            $table->text('narrative_outro')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['test_id', 'order']);
        });

        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('test_sections')->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(0);
            $table->string('type'); // single, multi, scale, text, ranking, situational, etc.
            $table->text('prompt');
            $table->text('helper')->nullable();
            $table->json('options')->nullable();
            $table->json('validation')->nullable();
            $table->json('scoring')->nullable();
            $table->boolean('required')->default(true);
            $table->timestamps();

            $table->index(['section_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('test_sections');
        Schema::dropIfExists('tests');
    }
};
