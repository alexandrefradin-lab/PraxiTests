<?php

/**
 * Pages publiques auto-alimentées (Landing + Structures).
 *
 * Règles couvertes :
 *  1. Les compteurs (épreuves, mini-apps) et la liste nominative des tests
 *     viennent des données réelles : ajouter un test publié ou activer un
 *     plugin mini-app met les deux pages à jour sans toucher aux vues.
 *  2. Périmètre identique aux pages candidat : un test-cadeau (qui vit dans
 *     la Salle du Trésor) ne compte pas parmi les épreuves de l'Armurerie.
 *  3. Un test non publié n'apparaît nulle part.
 */

use App\Models\Plugin;
use App\Models\Test;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush(); // reward_catalog_v2 + praxiquest.plugins.registry
});

function ppdcTest(string $slug, string $name, bool $published = true): Test
{
    return Test::create([
        'slug'              => $slug,
        'name'              => $name,
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => $published,
    ]);
}

function ppdcMiniApp(string $slug, ?string $testSlug = null): void
{
    $manifest = [
        'name'   => 'Mini ' . $slug,
        'reward' => ['threshold_eclats' => 1000],
    ];
    if ($testSlug !== null) {
        $manifest['test'] = ['slug' => $testSlug, 'name' => 'Mini ' . $slug];
    }

    Plugin::create([
        'slug'             => $slug,
        'name'             => 'Mini ' . $slug,
        'version'          => '1.0.0',
        'type'             => 'mini-app',
        'service_provider' => 'Test\\Provider',
        'enabled'          => true,
        'manifest'         => $manifest,
    ]);

    Cache::flush(); // le catalogue a pu être mis en cache avant la création
}

it('affiche sur la landing les compteurs calculés depuis les données', function () {
    ppdcTest('epreuve-a', 'Épreuve A');
    ppdcTest('epreuve-b', 'Épreuve B');
    ppdcTest('brouillon', 'Brouillon', published: false);
    ppdcMiniApp('mini-1');
    ppdcMiniApp('mini-2');

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Landing')
            ->where('testsCount', 2)
            ->where('miniAppsCount', 2));
});

it('exclut des épreuves les tests-cadeaux de la Salle du Trésor', function () {
    ppdcTest('epreuve-a', 'Épreuve A');
    ppdcTest('cadeau', 'Cadeau du Trésor');
    ppdcMiniApp('mini-cadeau', testSlug: 'cadeau');

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('testsCount', 1)
            ->where('miniAppsCount', 1));
});

it('liste sur la page structures les tests publiés et compte les mini-apps', function () {
    ppdcTest('epreuve-b', 'Épreuve B');
    ppdcTest('epreuve-a', 'Épreuve A');
    ppdcTest('brouillon', 'Brouillon', published: false);
    ppdcMiniApp('mini-1');

    $this->get('/structures')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Structures')
            ->where('tests', ['Épreuve A', 'Épreuve B'])
            ->where('miniAppsCount', 1));
});

it('reflète immédiatement un nouveau test et une nouvelle mini-app', function () {
    ppdcTest('epreuve-a', 'Épreuve A');

    $this->get('/')->assertInertia(fn ($page) => $page
        ->where('testsCount', 1)
        ->where('miniAppsCount', 0));

    ppdcTest('epreuve-nouvelle', 'Épreuve Nouvelle');
    ppdcMiniApp('mini-nouvelle');

    $this->get('/')->assertInertia(fn ($page) => $page
        ->where('testsCount', 2)
        ->where('miniAppsCount', 1));

    $this->get('/structures')->assertInertia(fn ($page) => $page
        ->where('tests', ['Épreuve A', 'Épreuve Nouvelle'])
        ->where('miniAppsCount', 1));
});
