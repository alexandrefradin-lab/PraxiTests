<?php

use Praxis\Plugins\PraxiMum\Archetypes\ArchetypeResolver;

it('returns 16 archetypes', function () {
    expect(ArchetypeResolver::all())->toHaveCount(16);
});

it('resolves Catalyseur on HHHHL profile', function () {
    $arch = ArchetypeResolver::resolve([
        'O' => ['T' => 60], 'C' => ['T' => 60], 'E' => ['T' => 60],
        'A' => ['T' => 60], 'N' => ['T' => 40],
    ]);
    expect($arch['matched_key'])->toBe('HHHHL');
    expect($arch['distance'])->toBe(0);
});

it('falls back to closest archetype when no exact match', function () {
    // HHHHH n'est pas dans le map → fallback Hamming
    $arch = ArchetypeResolver::resolve([
        'O' => ['T' => 60], 'C' => ['T' => 60], 'E' => ['T' => 60],
        'A' => ['T' => 60], 'N' => ['T' => 60],
    ]);
    expect($arch)->not->toBeNull();
    expect($arch['distance'])->toBeGreaterThan(0);
});

it('handles boundary T=50 as H (≥50)', function () {
    $arch = ArchetypeResolver::resolve([
        'O' => ['T' => 50], 'C' => ['T' => 50], 'E' => ['T' => 50],
        'A' => ['T' => 50], 'N' => ['T' => 50],
    ]);
    expect($arch['matched_key'][0])->toBe('H');
});

it('every archetype has required fields', function () {
    foreach (ArchetypeResolver::all() as $arch) {
        expect($arch)->toHaveKeys(['key', 'nom', 'tagline', 'emoji', 'description', 'rarete', 'couleur1', 'couleur2', 'traits']);
        expect($arch['traits'])->toBeArray()->not->toBeEmpty();
    }
});
