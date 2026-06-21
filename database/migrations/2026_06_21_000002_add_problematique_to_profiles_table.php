<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Problématique exprimée par le candidat avant l'upload du CV.
            // Alimente la synthèse IA et les pistes de métiers.
            $table->text('problematique')->nullable()->after('industry');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('problematique');
        });
    }
};
