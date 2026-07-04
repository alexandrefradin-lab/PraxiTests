<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HOTFIX prod 2026-07-04 — la table leads n'a PAS deleted_at en production
 * (le softDeletes() de la migration d'origine 2026_04_27_000008 a été ajouté
 * après son exécution en prod). Le trait SoftDeletes du modèle Lead ajoute
 * « where leads.deleted_at is null » à toutes les requêtes → 500 sur le
 * dashboard dès la connexion.
 *
 * Idempotent : ne fait rien si la colonne existe déjà (cas des bases neuves).
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('leads') && !Schema::hasColumn('leads', 'deleted_at')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Pas de down : la colonne est attendue par la migration d'origine.
    }
};
