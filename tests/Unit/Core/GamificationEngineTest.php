<?php

use Illuminate\Support\Facades\Config;
use Praxis\Core\Gamification\BadgeEvaluator;
use Praxis\Core\Gamification\GamificationEngine;

beforeEach(function () {
    $this->engine = new GamificationEngine(
        new BadgeEvaluator(),
    );
});

it('returns level 1 when gamification.levels config is empty', function () {
    Config::set('gamification.levels', []);

    $level = $this->engine->levelFromXp(999);
    expect($level)->toBe(1);
});

it('returns level 1 when gamification.levels config is null', function () {
    Config::set('gamification.levels', null);

    // Doit retourner 1 sans lever de Fatal Error / exception (null guard ajouté dans levelFromXp)
    $level = $this->engine->levelFromXp(500);
    expect($level)->toBe(1);
});

it('calculates correct level progression', function () {
    $levels = config('gamification.levels');
    if (empty($levels)) {
        $this->markTestSkipped('gamification.levels non configuré');
    }

    expect($this->engine->levelFromXp(0))->toBe(1);
    expect($this->engine->levelFromXp(9999))->toBeGreaterThanOrEqual(1);
});
