<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_norms', function (Blueprint $table) {
            $table->id();
            $table->string('test_slug', 120);           // 'praximet-riasec', 'praxiemo-eqi', etc.
            $table->string('dimension', 80);             // 'R', 'S', 'dim_1', 'autonomie', etc.
            $table->string('group_key', 60)->default('all'); // 'all', 'employee', 'jobseeker', etc.
            $table->unsignedInteger('n_responses')->default(0); // taille de l'échantillon de référence
            $table->float('mean');                       // moyenne dans les unités du scoring engine
            $table->float('std_dev');                   // écart-type
            $table->string('source', 300)->nullable();  // référence bibliographique
            $table->timestamp('computed_at')->nullable(); // si recalculé depuis les données plateforme
            $table->timestamps();

            $table->unique(['test_slug', 'dimension', 'group_key']);
            $table->index(['test_slug', 'group_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_norms');
    }
};
