<?php

use Praxis\Plugins\PraxiGuet\Models\GuetNotionProgress;

/*
 * Ancrage de La Tour de Guet : montée d'une boîte à la réussite, descente
 * d'UNE SEULE à l'erreur.
 *
 * La sévérité de cette règle décide à elle seule de la convergence du module.
 * Avec une remise à zéro, une notion réussie quatre fois d'affilée repartait
 * de rien au premier faux pas : simulé sur les 24 notions à 85 % de réussite,
 * l'ancrage complet passait de 19 à 134 sessions et de 213 à 3 194 cartes, et
 * seuls 8 % des parcours atteignaient 100 %. D'où les tests de non-régression
 * ci-dessous, en particulier « depuis la boîte 4 ».
 */

/** Progression détachée de la base : grade() ne fait aucun accès DB. */
function guetProgress(int $box = 0): GuetNotionProgress
{
    $p = new GuetNotionProgress(['user_id' => 1, 'notion_id' => 'n01']);
    $p->box = $box;

    return $p;
}

it('monte d une boite a chaque reussite', function () {
    $p = guetProgress(0);

    foreach ([1, 2, 3, 4] as $expected) {
        $p->grade(true, 0);
        expect($p->box)->toBe($expected);
    }
});

it('plafonne a la derniere boite', function () {
    $p = guetProgress(GuetNotionProgress::MAX_BOX);
    $p->grade(true, 0);

    expect($p->box)->toBe(GuetNotionProgress::MAX_BOX);
});

it('ne fait reculer que d une boite en cas d erreur', function () {
    $p = guetProgress(3);
    $p->grade(false, 0);

    expect($p->box)->toBe(2);
});

it('ne remet pas a zero une notion mure', function () {
    // Le coeur du correctif : quatre reussites puis une erreur laissent en
    // boite 3, pas en boite 0.
    $p = guetProgress(0);
    foreach (range(1, 4) as $_) {
        $p->grade(true, 0);
    }
    expect($p->box)->toBe(4);

    $p->grade(false, 0);
    expect($p->box)->toBe(3);
});

it('ne descend jamais sous la premiere boite', function () {
    $p = guetProgress(0);
    $p->grade(false, 0);

    expect($p->box)->toBe(0);
});

it('repousse l echeance selon la boite atteinte', function () {
    $p = guetProgress(0);

    // Boîte 1 après une réussite : intervalle de 1 session, depuis la 7e.
    $p->grade(true, 7);
    expect($p->box)->toBe(1)
        ->and($p->due_session)->toBe(7 + GuetNotionProgress::INTERVALS[1]);

    // Une notion en boîte 4 revient bien huit sessions plus tard.
    $mur = guetProgress(3);
    $mur->grade(true, 10);
    expect($mur->box)->toBe(4)
        ->and($mur->due_session)->toBe(10 + GuetNotionProgress::INTERVALS[4]);
});

it('raccourcit l echeance quand la notion redescend', function () {
    // Ratée depuis la boîte 4, elle retombe en 3 : elle revient après quatre
    // sessions au lieu de huit.
    $p = guetProgress(GuetNotionProgress::MAX_BOX);
    $p->grade(false, 5);

    expect($p->box)->toBe(3)
        ->and($p->due_session)->toBe(5 + GuetNotionProgress::INTERVALS[3]);
});

it('couvre chaque boite par un intervalle', function () {
    expect(GuetNotionProgress::INTERVALS)
        ->toHaveCount(GuetNotionProgress::MAX_BOX + 1);
});
