<?php

use Praxis\Core\Orientation\PtpPathService;

/*
 * Tests des fonctions pures du moteur de pistes PTP (sans base de données).
 * Garantissent l'intégrité des paliers et de l'indice d'opportunité.
 */

it('classe les paliers selon l\'écart de formation', function () {
    expect(PtpPathService::tierForGap(0))->toBe('accessible');
    expect(PtpPathService::tierForGap(1))->toBe('ptp');
    expect(PtpPathService::tierForGap(12))->toBe('ptp');
    expect(PtpPathService::tierForGap(13))->toBe('horizon');
    expect(PtpPathService::tierForGap(36))->toBe('horizon');
});

it('calcule une finançabilité décroissante bornée 0–100', function () {
    expect(PtpPathService::financability(0))->toBe(100);
    expect(PtpPathService::financability(13))->toBe(0);
    expect(PtpPathService::financability(24))->toBe(0);

    // Décroissance stricte entre 0 et la borne PTP.
    expect(PtpPathService::financability(6))
        ->toBeLessThan(PtpPathService::financability(3));
    expect(PtpPathService::financability(6))->toBeGreaterThan(0);
});

it('borne le score de marché entre 0 et 100', function () {
    expect(PtpPathService::marketScore('fort', 'croissance'))->toBe(100);
    expect(PtpPathService::marketScore('faible', 'declin'))->toBe(15);
    expect(PtpPathService::marketScore('moyen', 'stable'))->toBe(65);
    expect(PtpPathService::marketScore('inconnu', 'inconnu'))->toBe(50);
});

it('compose un indice d\'opportunité borné 0–100', function () {
    $best  = PtpPathService::opportunityIndex(100, 0, 'fort', 'croissance');
    $worst = PtpPathService::opportunityIndex(0, 36, 'faible', 'declin');

    expect($best)->toBe(100);
    expect($worst)->toBeGreaterThanOrEqual(0);
    expect($best)->toBeGreaterThan($worst);
});

it('favorise une piste finançable à fit égal', function () {
    $accessible = PtpPathService::opportunityIndex(70, 0, 'moyen', 'stable');
    $horizon    = PtpPathService::opportunityIndex(70, 24, 'moyen', 'stable');

    expect($accessible)->toBeGreaterThan($horizon);
});
