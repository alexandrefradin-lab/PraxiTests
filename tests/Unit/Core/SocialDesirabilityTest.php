<?php

use Praxis\Core\Scoring\SocialDesirability;

// ── Échelle praxiemo : 6 items, 1-4 → plage 6-24 (seuils 12 / 18) ────────────

it('flags strong bias at the praxiemo threshold (sum 12 on 6-24)', function () {
    $r = SocialDesirability::fromControlSum(sum: 12, answered: 6, itemCount: 6, itemMax: 4, messageFort: 'msg');
    expect($r['niveau'])->toBe(SocialDesirability::FORT);
    expect($r['alerte'])->toBeTrue();
    expect($r['message'])->toBe('msg');
    expect($r['score'])->toBe(12);
});

it('flags moderate bias just above the strong threshold (sum 13 on 6-24)', function () {
    $r = SocialDesirability::fromControlSum(sum: 13, answered: 6, itemCount: 6, itemMax: 4);
    expect($r['niveau'])->toBe(SocialDesirability::MODERE);
    expect($r['alerte'])->toBeFalse();
});

it('reports reliable answers above the moderate threshold (sum 19 on 6-24)', function () {
    $r = SocialDesirability::fromControlSum(sum: 19, answered: 6, itemCount: 6, itemMax: 4);
    expect($r['niveau'])->toBe(SocialDesirability::FIABLE);
});

// ── Échelle praxisens : 6 items, 1-5 → plage 6-30 (seuils proportionnels 14 / 22) ──

it('scales thresholds proportionally on a 1-5 scale (sum 14 = strong, 15 = moderate)', function () {
    $fort = SocialDesirability::fromControlSum(sum: 14, answered: 6, itemCount: 6, itemMax: 5);
    $modere = SocialDesirability::fromControlSum(sum: 15, answered: 6, itemCount: 6, itemMax: 5);
    expect($fort['niveau'])->toBe(SocialDesirability::FORT);
    expect($modere['niveau'])->toBe(SocialDesirability::MODERE);
});

it('scales the reliable threshold proportionally on a 1-5 scale (22 = moderate, 23 = reliable)', function () {
    $modere = SocialDesirability::fromControlSum(sum: 22, answered: 6, itemCount: 6, itemMax: 5);
    $fiable = SocialDesirability::fromControlSum(sum: 23, answered: 6, itemCount: 6, itemMax: 5);
    expect($modere['niveau'])->toBe(SocialDesirability::MODERE);
    expect($fiable['niveau'])->toBe(SocialDesirability::FIABLE);
});

// ── Conventions communes ─────────────────────────────────────────────────────

it('counts missing control items at the scale minimum', function () {
    // 3 items répondus au max (15) + 3 manquants comptés à 1 → 18 ≤ 22 : modéré.
    // Sans la pénalité, 15 + rien = un profil incomplet paraîtrait plus fiable.
    $r = SocialDesirability::fromControlSum(sum: 15, answered: 3, itemCount: 6, itemMax: 5);
    expect($r['score'])->toBe(18);
    expect($r['niveau'])->toBe(SocialDesirability::MODERE);
});

it('returns Non mesuré without correction when no control item was answered', function () {
    $r = SocialDesirability::fromControlSum(sum: 0, answered: 0, itemCount: 6, itemMax: 5);
    expect($r['niveau'])->toBe(SocialDesirability::NON_MESURE);
    expect($r['score'])->toBeNull();
    expect($r['alerte'])->toBeFalse();
    expect(SocialDesirability::shrinkFactor($r['niveau']))->toBe(1.0);
});

// ── Variante praximum : pourcentage de biais (haut = biais), seuils 60 / 75 ──

it('maps bias percentages to levels with the praximum thresholds', function () {
    expect(SocialDesirability::levelFromBiasPercent(59))->toBe(SocialDesirability::FIABLE);
    expect(SocialDesirability::levelFromBiasPercent(60))->toBe(SocialDesirability::MODERE);
    expect(SocialDesirability::levelFromBiasPercent(74))->toBe(SocialDesirability::MODERE);
    expect(SocialDesirability::levelFromBiasPercent(75))->toBe(SocialDesirability::FORT);
});

// ── Correction douce ─────────────────────────────────────────────────────────

it('shrinks values toward the midpoint with the factor of the bias level', function () {
    expect(SocialDesirability::shrinkFactor(SocialDesirability::FORT))->toBe(0.80);
    expect(SocialDesirability::shrinkFactor(SocialDesirability::MODERE))->toBe(0.90);
    expect(SocialDesirability::shrinkFactor(SocialDesirability::FIABLE))->toBe(1.0);

    // 5,0 régressé vers 3,0 à 0,80 → 4,6 (cas de référence des tests praxisens)
    expect(SocialDesirability::shrink(5.0, 3.0, 0.80))->toBe(4.6);
    // La correction est symétrique : un score bas remonte vers le milieu.
    expect(SocialDesirability::shrink(1.0, 3.0, 0.80))->toBe(1.4);
    // Facteur neutre = valeur inchangée.
    expect(SocialDesirability::shrink(4.2, 3.0, 1.0))->toBe(4.2);
});
