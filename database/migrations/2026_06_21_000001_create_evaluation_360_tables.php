<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feedback 360° : un candidat (sujet) ouvre un « panel », invite des évaluateurs
 * (manager / pairs / collaborateurs) qui se positionnent anonymement sur son
 * fonctionnement. Les réponses des évaluateurs sont stockées comme des
 * TestAttempt « invités » (user_id null) reliés au panel via panel_id.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_panels', function (Blueprint $table) {
            $table->id();
            // Le candidat évalué (sujet du 360).
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            // Sa propre auto-évaluation (référence pour les écarts self / autres).
            $table->foreignId('self_attempt_id')->nullable()
                ->constrained('test_attempts')->nullOnDelete();
            $table->enum('status', ['open', 'closed'])->default('open');
            // Nombre minimal d'évaluateurs par catégorie pour lever l'anonymat.
            $table->unsignedTinyInteger('anonymity_threshold')->default(3);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'test_id']);
        });

        Schema::create('evaluation_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('evaluation_panels')->cascadeOnDelete();
            $table->enum('relation', ['manager', 'pair', 'collaborateur']);
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'sent', 'opened', 'completed', 'declined'])
                ->default('pending');
            // L'attempt « invité » créé quand l'évaluateur commence à répondre.
            $table->foreignId('attempt_id')->nullable()
                ->constrained('test_attempts')->nullOnDelete();
            // Réponses libres (verbatims) : { strength, growth, advice }.
            $table->json('verbatims')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['panel_id', 'status']);
            $table->index(['email', 'panel_id']);
        });

        // Rattacher un attempt à un panel + qualifier le regard (self / manager / …).
        Schema::table('test_attempts', function (Blueprint $table) {
            $table->foreignId('panel_id')->nullable()->after('invitation_id')
                ->constrained('evaluation_panels')->nullOnDelete();
            $table->string('rater_relation', 20)->nullable()->after('panel_id');
        });
    }

    public function down(): void
    {
        Schema::table('test_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('panel_id');
            $table->dropColumn('rater_relation');
        });
        Schema::dropIfExists('evaluation_invitations');
        Schema::dropIfExists('evaluation_panels');
    }
};
