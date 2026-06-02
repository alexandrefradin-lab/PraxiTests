<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('professional_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('subdomain')->unique()->nullable();
            $table->string('custom_domain')->unique()->nullable();
            $table->string('plan')->default('trial');
            $table->json('branding')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('seats_limit')->default(5);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('professional_account_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member'); // owner, admin, member, viewer
            $table->timestamps();

            $table->unique(['professional_account_id', 'user_id'], 'pro_account_users_unique');
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable(); // landing, referral, campaign:xxx
            $table->unsignedTinyInteger('score')->default(0);
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost'])->default('new');
            $table->json('utm')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['email']);
            $table->index(['professional_account_id', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('resource_type')->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('professional_account_users');
        Schema::dropIfExists('professional_accounts');
    }
};
