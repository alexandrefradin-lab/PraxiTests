<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Résultat calculé des pistes par candidat (cache recalculable).
 *
 * 1 ligne par (profile, career_path). Le fit_score dérive des tests et reste figé
 * (intégrité de la mesure) ; formation_gap_months / tier / unlocked évoluent quand
 * la personne déclare/valide des acquis de formation (mécanique de déblocage).
 *
 *  - tier : accessible (0 formation) | ptp (≤ 12 mois, finançable) | horizon (> 12 mois)
 *  - opportunity_index : f(fit, finançabilité, marché) → classe les pistes
 *
 * Voir PLAN-PISTES-DYNAMIQUES-PTP.md.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('profile_path_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('career_path_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('fit_score')->default(0);             // 0–100, figé
            $table->unsignedSmallInteger('formation_gap_months')->default(0);
            $table->enum('tier', ['accessible', 'ptp', 'horizon'])->default('horizon')->index();
            $table->unsignedTinyInteger('opportunity_index')->default(0)->index();
            $table->boolean('unlocked')->default(false);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'career_path_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_path_matches');
    }
};
