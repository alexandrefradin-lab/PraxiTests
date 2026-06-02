<?php

use App\Models\Test;
use Praxis\Core\TestEngine\TestEngine;

it('throws InvalidArgumentException for unknown scoring engine', function () {
    $engine = new TestEngine();

    $test = new Test();
    $test->scoring_engine = 'engine-qui-nexiste-pas';

    expect(fn () => $engine->resolveScoringEngine($test))
        ->toThrow(\InvalidArgumentException::class);
});

it('resolves the default scoring engine', function () {
    $engine = new TestEngine();

    $test = new Test();
    $test->scoring_engine = 'default';

    $resolved = $engine->resolveScoringEngine($test);
    expect($resolved->key())->toBe('default');
});

it('availableEngines returns at least the default engine', function () {
    $engine = new TestEngine();
    expect($engine->availableEngines())->toContain('default');
});
