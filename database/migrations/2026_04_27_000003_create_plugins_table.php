<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('version');
            $table->string('author')->nullable();
            $table->string('type'); // test, scoring, ai, mail, gamification, integration, theme, reporting
            $table->text('description')->nullable();
            $table->string('service_provider');
            $table->json('manifest');
            $table->json('config')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('core')->default(false); // core plugins can't be uninstalled
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('last_activated_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'enabled']);
        });

        Schema::create('plugin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('level'); // info, warning, error
            $table->string('event');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['plugin_id', 'level', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_logs');
        Schema::dropIfExists('plugins');
    }
};
