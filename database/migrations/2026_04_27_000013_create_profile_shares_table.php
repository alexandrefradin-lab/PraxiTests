<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotence : cette migration a été renommée (2024_01_01_000010 →
        // 2026_04_27_000013) pour corriger l'ordre des FK. Sur une prod déjà
        // migrée, la table existe déjà sous l'ancien nom d'enregistrement ;
        // sans ce garde, `migrate` tenterait de la recréer et échouerait.
        if (Schema::hasTable('profile_shares')) {
            return;
        }

        Schema::create('profile_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable(); // null = jamais
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['token', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_shares');
    }
};
