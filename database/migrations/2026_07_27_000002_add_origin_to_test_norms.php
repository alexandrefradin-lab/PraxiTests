<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sépare les normes de référence (littérature scientifique / provisoires
 * seedées) des normes auto-calculées depuis les passations plateforme.
 *
 *   origin = 'reference' — seedées, jamais touchées par le recalcul auto
 *   origin = 'platform'  — écrites uniquement par NormInterpreter::recompute*
 *
 * Les deux coexistent pour la même (test_slug, dimension, group_key) : le
 * choix se fait à la lecture selon les seuils d'échantillon (NormInterpreter).
 * Les lignes existantes deviennent 'reference' (défaut), y compris celles
 * historiquement auto-calculées — le prochain recalcul hebdo recrée leurs
 * équivalents 'platform' sans perte.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_norms', function (Blueprint $table) {
            $table->string('origin', 20)->default('reference')->after('group_key');
        });

        Schema::table('test_norms', function (Blueprint $table) {
            $table->dropUnique(['test_slug', 'dimension', 'group_key']);
            $table->unique(['test_slug', 'dimension', 'group_key', 'origin'], 'test_norms_slug_dim_group_origin_unique');
        });
    }

    public function down(): void
    {
        // Supprimer d'abord les doublons potentiels (platform) avant de
        // restaurer l'unicité à 3 colonnes.
        \Illuminate\Support\Facades\DB::table('test_norms')->where('origin', 'platform')->delete();

        Schema::table('test_norms', function (Blueprint $table) {
            $table->dropUnique('test_norms_slug_dim_group_origin_unique');
            $table->unique(['test_slug', 'dimension', 'group_key']);
            $table->dropColumn('origin');
        });
    }
};
