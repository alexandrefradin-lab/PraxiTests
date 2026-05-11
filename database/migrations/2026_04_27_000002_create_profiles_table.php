<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', ['employee', 'entrepreneur', 'jobseeker', 'student', 'other'])->nullable();
            $table->date('status_since')->nullable();
            $table->unsignedSmallInteger('status_months')->nullable();
            $table->string('current_role')->nullable();
            $table->string('industry')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('cv_original_name')->nullable();
            $table->longText('cv_extracted_text')->nullable();
            $table->json('cv_structured')->nullable(); // skills, experiences, education extracted
            $table->json('preferences')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('consent_data')->default(false);
            $table->boolean('consent_marketing')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'status_since']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
