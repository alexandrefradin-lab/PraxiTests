<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Libellés corporate des badges.
 *
 * Les noms en base sont écrits au registre « quête » (« Éveillé », « Rapide
 * comme l'éclair ») et détonnent dans le parcours professionnel. Colonnes
 * nullables : un badge sans variante retombe sur name/description.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('name_corporate')->nullable()->after('name');
            $table->text('description_corporate')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['name_corporate', 'description_corporate']);
        });
    }
};
