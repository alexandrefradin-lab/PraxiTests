<?php

use Praxis\Plugins\PraxiEmo\Scoring\EqiScoringEngine;

beforeEach(function () {
    $this->engine = new EqiScoringEngine();
});

it('returns empty top_dev when all dim scores are above threshold', function () {
    // Réponse 4 partout = scores maximaux (20/dim) = aucune dimension ≤ 12
    $attempt = praxiemoAttempt(answer: 4);
    $result = $this->engine->score($attempt);

    // top_dev doit être un tableau (vide dans ce cas), pas une erreur
    expect($result)->toHaveKey('top_dev');
    expect($result['top_dev'])->toBeArray();
    expect($result['top_dev'])->toBeEmpty();
});

it('returns non-empty top_dev when some scores are low', function () {
    // Réponse 1 partout = scores minimaux (5/dim) = toutes dimensions ≤ 12 → en développement
    $attempt = praxiemoAttempt(answer: 1);
    $result = $this->engine->score($attempt);

    expect($result)->toHaveKey('top_dev');
    expect($result['top_dev'])->toBeArray();
    // Avec des scores minimaux, on s'attend à des dimensions en développement (limité à 3 par l'engine)
    expect(count($result['top_dev']))->toBeGreaterThanOrEqual(1);
});

it('score result has required keys', function () {
    $attempt = praxiemoAttempt(answer: 3);
    $result = $this->engine->score($attempt);

    expect($result)->toHaveKeys(['dim_scores', 'score_global', 'top_dev', 'desirabilite']);
});
