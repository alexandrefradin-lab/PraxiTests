<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des pistes métiers (PTP) — catalogue partagé.
 *
 * Chaque ligne = un métier cible vers lequel une transition est possible.
 * Le score d'un test ne bouge jamais ; ce sont les pistes ouvertes qui évoluent.
 *
 *  - formation_months : durée estimée pour combler l'écart "standard" (niveau 1 = estim. famille)
 *  - market_*         : signaux marché de l'emploi (niveau 1 = saisis à la main)
 *  - rome_code        : mapping France Travail (offres, tension, BMO) — renseigné en Lot 2
 *  - rncp_codes       : certifications cibles (formation / éligibilité PTP) — Lot 2
 *  - fit_dimensions   : clés de dimensions de scoring qui nourrissent le fit
 *
 * Voir PLAN-PISTES-DYNAMIQUES-PTP.md.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('career_paths', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('family')->index();

            $table->string('rome_code')->nullable();
            $table->json('rncp_codes')->nullable();

            $table->unsignedSmallInteger('formation_months')->default(0);
            $table->enum('market_demand', ['faible', 'moyen', 'fort'])->default('moyen');
            $table->enum('market_trend', ['declin', 'stable', 'croissance'])->default('stable');
            $table->json('salary_indicative')->nullable();   // {min, max, median, currency}
            $table->json('fit_dimensions')->nullable();       // ["I","A",...] clés de scoring

            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_paths');
    }
};
