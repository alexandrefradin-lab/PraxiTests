<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Socle du dispositif anti-copie (cf. config/protection.php).
 *
 * - user_devices      : empreintes d'appareils par compte, base de la détection
 *                       de partage / revente d'accès.
 * - protection_alerts : journal unique des anomalies (aspiration de contenu,
 *                       partage de compte, exécution hors licence, fuite PDF).
 *                       Séparé de audit_logs, qui trace les actions ADMIN
 *                       légitimes : on ne veut pas noyer l'un dans l'autre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // SHA-256 tronqué de (user-agent + langue + APP_KEY). Pas d'empreinte
            // invasive type canvas : on reste sur ce que le navigateur envoie
            // déjà spontanément, pour ne pas alourdir la conformité RGPD.
            $table->string('fingerprint', 64);
            $table->string('label', 120)->nullable();       // « Chrome / Windows »
            $table->string('last_ip', 45)->nullable();
            $table->string('last_network', 45)->nullable(); // préfixe /24, pour compter les réseaux
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->boolean('trusted')->default(false);     // appareil validé manuellement → jamais compté comme anomalie
            $table->timestamps();

            $table->unique(['user_id', 'fingerprint']);
            $table->index(['user_id', 'last_seen_at']);
        });

        Schema::create('protection_alerts', function (Blueprint $table) {
            $table->id();
            // Nullable : une aspiration de contenu peut venir d'un visiteur non
            // authentifié, et une exécution hors licence n'a pas d'utilisateur.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);                     // scraping | sharing | license | pdf_leak
            $table->string('severity', 20)->default('warning'); // info | warning | critical
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->text('summary');
            $table->json('context')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protection_alerts');
        Schema::dropIfExists('user_devices');
    }
};
