<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tranche d'âge et niveau de diplôme — facultatifs, collectés à l'onboarding.
 *
 * Finalité (RGPD) : étalonnage des tests par sous-groupes (normes par tranche
 * d'âge) et interprétation contextualisée des résultats. Minimisation : on
 * stocke des tranches larges, jamais de date de naissance ni d'intitulé exact.
 *
 * Valeurs autorisées : config('praxiquest.profile.age_bands') et
 * config('praxiquest.profile.education_levels').
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('age_band', 10)->nullable()->after('status_months');
            $table->string('education_level', 20)->nullable()->after('age_band');

            $table->index('age_band');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['age_band']);
            $table->dropColumn(['age_band', 'education_level']);
        });
    }
};
