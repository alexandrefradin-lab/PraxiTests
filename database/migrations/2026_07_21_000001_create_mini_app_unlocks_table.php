<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Déblocage CHOISI des mini-apps de La Salle du Trésor.
 *
 * Avant : le déblocage était recalculé à chaque requête (totalEclats >= seuil),
 * donc jamais persisté. Introduire une dépense d'Éclats sans cette table
 * reverrouillerait mécaniquement les trésors déjà ouverts dès le premier achat.
 *
 * cost_eclats mémorise le prix RÉELLEMENT payé : les déblocages repris de
 * l'ancien système (migration de reprise) valent 0 pour ne pas vider
 * rétroactivement le portefeuille des candidats existants.
 *
 * NB : cette table est en database/migrations/ (et non dans un plugin) car
 * elle est lue par du code du core — les migrations de plugins ne tournent
 * pas en test (table plugins vide).
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mini_app_unlocks')) {
            return;
        }

        Schema::create('mini_app_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plugin_slug');
            $table->unsignedInteger('cost_eclats')->default(0);
            $table->timestamp('unlocked_at')->useCurrent();
            $table->timestamps();

            // Garde-fou anti double-déblocage (double-clic, requêtes parallèles) :
            // le service s'appuie dessus plutôt que sur un check-then-act.
            $table->unique(['user_id', 'plugin_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_app_unlocks');
    }
};
