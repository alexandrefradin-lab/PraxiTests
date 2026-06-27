<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Onglet « Ton métier face à l'IA » du Grimoire.
 *
 * Stocke une relecture IA dédiée : comment le métier actuel du candidat (ou son
 * statut/parcours) est susceptible d'être transformé par l'intelligence
 * artificielle — tâches automatisables, compétences à renforcer, opportunités.
 * Texte Markdown, généré en même temps que les voies (étape 2 du job).
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('profile_grimoires')) {
            return;
        }
        if (Schema::hasColumn('profile_grimoires', 'ia_impact')) {
            return;
        }
        Schema::table('profile_grimoires', function (Blueprint $table) {
            $table->longText('ia_impact')->nullable()->after('voies');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('profile_grimoires', 'ia_impact')) {
            Schema::table('profile_grimoires', function (Blueprint $table) {
                $table->dropColumn('ia_impact');
            });
        }
    }
};
