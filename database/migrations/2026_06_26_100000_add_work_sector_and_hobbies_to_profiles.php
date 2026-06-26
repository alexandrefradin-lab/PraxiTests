<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Secteur d'emploi : public / privé / indépendant / association
            $table->string('work_sector', 30)->nullable()->after('industry');
            // Loisirs / hobbies (texte libre, optionnel)
            $table->text('hobbies')->nullable()->after('work_sector');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['work_sector', 'hobbies']);
        });
    }
};
