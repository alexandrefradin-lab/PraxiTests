<?php

/**
 * Garde-fou du TestNormsSeeder (audit 27/07/2026).
 *
 * Le seeder dépliait un tableau associatif de façon positionnelle
 * (`foreach ($riasec as $dim => [$mean, $sd, $n])`) → « Undefined array key 0 »
 * dès qu'on l'exécutait. Bug jamais vu car le seeder n'est pas dans le pipeline
 * de déploiement. Ce test l'exécute réellement pour interdire toute régression,
 * et verrouille l'honnêteté des barèmes (pas de fausse citation, n_responses=0).
 */

use Illuminate\Support\Facades\DB;

it('exécute TestNormsSeeder sans erreur et insère les barèmes de référence', function () {
    $this->seed(\Database\Seeders\TestNormsSeeder::class);

    // RIASEC : 6 dimensions (la boucle qui plantait auparavant).
    expect(DB::table('test_norms')->where('test_slug', 'praximet-riasec')->count())->toBe(6)
        ->and(DB::table('test_norms')->where('test_slug', 'praxiemo-eqi')->count())->toBe(16)
        ->and(DB::table('test_norms')->where('test_slug', 'praxivaleurs-schwartz')->count())->toBe(10)
        ->and(DB::table('test_norms')->where('test_slug', 'praximum-bigfive')->count())->toBe(5);
});

it('pose des barèmes honnêtes (aucune fausse citation, n_responses=0)', function () {
    $this->seed(\Database\Seeders\TestNormsSeeder::class);

    $rows = DB::table('test_norms')->get();

    foreach ($rows as $row) {
        // Aucune source ne doit revendiquer une publication/échantillon externe.
        expect($row->source)->not->toContain('Bar-On')
            ->and($row->source)->not->toContain('Holland')
            ->and($row->source)->not->toContain('N≈')
            ->and((int) $row->n_responses)->toBe(0);
    }
});

it('est idempotent (deux passages ne dupliquent pas les lignes)', function () {
    $this->seed(\Database\Seeders\TestNormsSeeder::class);
    $after1 = DB::table('test_norms')->count();

    $this->seed(\Database\Seeders\TestNormsSeeder::class);
    $after2 = DB::table('test_norms')->count();

    expect($after2)->toBe($after1);
});
