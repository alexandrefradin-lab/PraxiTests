<?php

use App\Support\ChartRenderer;

/*
 * Tests des graphiques du rapport PDF (sans base de données).
 *
 * Le fond blanc opaque est vérifié explicitement : un fond transparent ne
 * survivait pas au rééchantillonnage et ressortait en aplat gris derrière le
 * graphique — invisible dans le code comme dans les mesures de mise en page,
 * visible seulement à l'œil sur la page imprimée.
 */

/** Décode le data-URI renvoyé par ChartRenderer en ressource GD. */
function chartImage(string $uri)
{
    expect($uri)->toStartWith('data:image/png;base64,');
    $png = base64_decode(explode(',', $uri, 2)[1]);
    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");

    return imagecreatefromstring($png);
}

/** Couleur d'un pixel au format #RRGGBB. */
function chartPixel($im, int $x, int $y): string
{
    $c = imagecolorat($im, $x, $y);

    return sprintf('#%02X%02X%02X', ($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF);
}

$axes = [
    ['label' => 'Utilité sociale', 'value' => 91],
    ['label' => 'Autonomie',       'value' => 84],
    ['label' => 'Coopération',     'value' => 77],
];

it('ne rend pas de radar en dessous de trois axes', function () use ($axes) {
    expect(ChartRenderer::radar(array_slice($axes, 0, 2)))->toBeNull();
});

it('rend un radar sur fond parfaitement blanc', function () use ($axes) {
    $uri = ChartRenderer::radar($axes, ['accent' => '#A67520']);
    expect($uri)->not->toBeNull();

    $im = chartImage($uri);
    expect(imagesx($im))->toBe(480)
        ->and(imagesy($im))->toBe(420);

    // Les quatre coins : aucun aplat parasite, la vignette se fond dans le papier.
    foreach ([[2, 2], [477, 2], [2, 417], [477, 417]] as [$x, $y]) {
        expect(chartPixel($im, $x, $y))->toBe('#FFFFFF');
    }
})->skip(fn () => ! extension_loaded('gd'), 'GD absent');

it('ne rend pas de quadrant sans les axes de Karasek', function () {
    expect(ChartRenderer::karasekQuadrant(['demandes' => 20]))->toBeNull();
});

it('rend le quadrant de Karasek sur fond parfaitement blanc', function () {
    $uri = ChartRenderer::karasekQuadrant(
        ['demandes' => 27.0, 'latitude' => 15.5],
        ['tendu' => ['color' => '#7B1515', 'label' => 'Tendu']],
        'tendu'
    );
    expect($uri)->not->toBeNull();

    $im = chartImage($uri);
    expect(chartPixel($im, 2, 2))->toBe('#FFFFFF');
})->skip(fn () => ! extension_loaded('gd'), 'GD absent');
