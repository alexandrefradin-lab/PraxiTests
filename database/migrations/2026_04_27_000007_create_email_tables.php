<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->json('variants')->nullable(); // A/B test
            $table->json('audience_filter')->nullable();
            $table->json('neuromarketing')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('stats')->nullable(); // delivered, opened, clicked, bounced
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('email_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('trigger_event'); // attempt.completed, lead.captured, inactive_7d, etc.
            $table->json('audience_filter')->nullable();
            $table->json('steps'); // [{delay_hours, subject, body, conditions}]
            $table->boolean('enabled')->default(true);
            $table->json('stats')->nullable();
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();
            $table->foreignId('sequence_id')->nullable()->constrained('email_sequences')->nullOnDelete();
            $table->unsignedTinyInteger('step')->nullable();
            $table->string('to_email');
            $table->string('subject');
            $table->string('variant')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'])->default('queued');
            $table->json('headers')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'sent_at']);
            $table->index('to_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_sequences');
        Schema::dropIfExists('email_campaigns');
    }
};
