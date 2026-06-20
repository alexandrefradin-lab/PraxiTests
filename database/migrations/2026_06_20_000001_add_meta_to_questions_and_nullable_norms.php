<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Colonne `meta` (JSON) sur les questions : utilisée par les plugins
        // mini-app / assessment (praxiself, praxis360…) pour stocker des
        // métadonnées d'exercice hors champ `scoring`.
        if (! Schema::hasColumn('test_questions', 'meta')) {
            Schema::table('test_questions', function (Blueprint $table) {
                $table->json('meta')->nullable()->after('scoring');
            });
        }

        // Normes : `mean` / `std_dev` rendus nullables. Un test sans norme
        // publiée insère null ("à calculer après 50 passations", cf. NormsSeeder).
        Schema::table('test_norms', function (Blueprint $table) {
            $table->float('mean')->nullable()->change();
            $table->float('std_dev')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('test_questions', 'meta')) {
            Schema::table('test_questions', function (Blueprint $table) {
                $table->dropColumn('meta');
            });
        }
        // mean/std_dev : on ne les repasse pas NOT NULL (des null peuvent exister).
    }
};
