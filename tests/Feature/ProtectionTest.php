<?php

/**
 * Tests Feature du dispositif anti-copie (cf. config/protection.php).
 *
 * 1. Licence : un jeton valide passe, un domaine hors licence est refusé,
 *    une signature retouchée est rejetée, l'expiration respecte la tolérance.
 * 2. Anti-scraping : les outils d'aspiration sont repérés, la cadence
 *    excessive déclenche un blocage, l'usage normal passe.
 * 3. Partage de comptes : au-delà du plafond d'appareils, une alerte tombe ;
 *    les candidats et les admins ne sont pas surveillés.
 * 4. Tatouage PDF : le code est stable, distinct par compte, et résoluble.
 */

use App\Models\ProtectionAlert;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Cache;
use Praxis\Core\Protection\DeviceGuard;
use Praxis\Core\Protection\DocumentWatermark;
use Praxis\Core\Protection\LicenseService;
use Praxis\Core\Protection\ScrapingGuard;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Cache::flush();
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

/** Paire de clés RSA jetable, générée à la volée pour le test. */
function protMakeKeyPair(): array
{
    $resource = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    openssl_pkey_export($resource, $private);

    return [$private, openssl_pkey_get_details($resource)['key']];
}

/** Installe une licence signée dans la configuration courante. */
function protInstallLicense(array $claims = []): void
{
    [$private, $public] = protMakeKeyPair();

    $token = LicenseService::sign(array_merge([
        'v'          => 1,
        'id'         => 'PQ-TEST-0001',
        'licensee'   => 'Praxis Accompagnement',
        'edition'    => 'saas',
        'domains'    => ['praxiquest.fr', '*.praxiquest.fr'],
        'issued_at'  => now()->toDateString(),
        'expires_at' => now()->addYear()->toDateString(),
    ], $claims), $private);

    config([
        'protection.license.enabled'    => true,
        'protection.license.key'        => $token,
        'protection.license.public_key' => $public,
        'protection.license.cache_ttl'  => 0,
        'protection.license.grace_days' => 14,
        'protection.license.mode'       => 'warn',
    ]);

    // Le service mémorise son verdict pour la durée de la requête : sans purge,
    // un test qui réinstalle une licence relirait l'ancien résultat.
    app(LicenseService::class)->flush('praxiquest.fr');
}

/** Passe une requête au middleware de licence, renvoie la réponse produite. */
function protHandleLicense(string $url): Symfony\Component\HttpFoundation\Response
{
    return app(App\Http\Middleware\VerifyLicense::class)->handle(
        Illuminate\Http\Request::create($url, 'GET'),
        fn () => new Illuminate\Http\Response('ok'),
    );
}

// ─── 1. Licence ───────────────────────────────────────────────────────────────

it('accepte une licence valide sur un domaine licencié', function () {
    protInstallLicense();

    $status = app(LicenseService::class)->status('praxiquest.fr');

    expect($status->status)->toBe(LicenseService::STATUS_VALID)
        ->and($status->allowsExecution())->toBeTrue();
});

it('couvre les sous-domaines par le joker', function () {
    protInstallLicense();

    expect(app(LicenseService::class)->passes('app.praxiquest.fr'))->toBeTrue();
});

it('refuse un domaine hors licence — cas du code redéployé ailleurs', function () {
    protInstallLicense();

    $status = app(LicenseService::class)->status('praxiquest-clone.com');

    expect($status->status)->toBe(LicenseService::STATUS_DOMAIN)
        ->and($status->allowsExecution())->toBeFalse()
        ->and($status->looksLikeCopy())->toBeTrue();
});

it('rejette une licence dont la charge utile a été retouchée', function () {
    protInstallLicense();

    // Le copieur réécrit les domaines sans pouvoir resigner : la signature
    // ne correspond plus à la charge utile.
    [$payload, $signature] = explode('.', config('protection.license.key'));
    $claims = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    $claims['domains'] = ['praxiquest-clone.com'];

    $forged = rtrim(strtr(base64_encode(json_encode($claims)), '+/', '-_'), '=') . '.' . $signature;
    config(['protection.license.key' => $forged]);

    $status = app(LicenseService::class)->status('praxiquest-clone.com');

    expect($status->status)->toBe(LicenseService::STATUS_FORGED)
        ->and($status->allowsExecution())->toBeFalse();
});

it('tolère une licence expirée pendant la période de grâce puis la refuse', function () {
    protInstallLicense(['expires_at' => now()->subDays(3)->toDateString()]);

    expect(app(LicenseService::class)->status('praxiquest.fr')->status)
        ->toBe(LicenseService::STATUS_GRACE);

    // Au-delà de la tolérance, l'exécution n'est plus autorisée.
    protInstallLicense(['expires_at' => now()->subDays(30)->toDateString()]);
    $status = app(LicenseService::class)->status('praxiquest.fr');

    expect($status->status)->toBe(LicenseService::STATUS_EXPIRED)
        ->and($status->allowsExecution())->toBeFalse();
});

it('signale une licence absente sans planter', function () {
    config([
        'protection.license.enabled'   => true,
        'protection.license.key'       => null,
        'protection.license.cache_ttl' => 0,
    ]);

    expect(app(LicenseService::class)->status('praxiquest.fr')->status)
        ->toBe(LicenseService::STATUS_MISSING);
});

it('laisse passer les requêtes quand le contrôle de licence est désactivé', function () {
    config(['protection.license.enabled' => false, 'protection.license.key' => null]);

    expect(protHandleLicense('https://praxiquest-clone.com/tableau-de-bord')->getContent())
        ->toBe('ok');
});

it('journalise sans couper en mode warn, coupe en mode block', function () {
    protInstallLicense();

    // Mode warn : l'anomalie part au journal, la requête passe quand même.
    expect(protHandleLicense('https://praxiquest-clone.com/tableau-de-bord')->getContent())
        ->toBe('ok');

    config(['protection.license.mode' => 'block']);
    app(LicenseService::class)->flush('praxiquest-clone.com');

    expect(fn () => protHandleLicense('https://praxiquest-clone.com/tableau-de-bord'))
        ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('sert toujours le health-check et le webhook Stripe, licence ou non', function () {
    protInstallLicense();
    config(['protection.license.mode' => 'block']);

    // Couper /up ferait tomber la supervision, couper le webhook ferait perdre
    // des événements de facturation : ces chemins restent servis.
    expect(protHandleLicense('https://praxiquest-clone.com/up')->getContent())->toBe('ok')
        ->and(protHandleLicense('https://praxiquest-clone.com/stripe/webhook')->getContent())->toBe('ok');
});

// ─── 2. Anti-scraping ─────────────────────────────────────────────────────────

it('repère un outil d\'aspiration à son user-agent', function () {
    config([
        'protection.scraping.enabled' => true,
        'protection.scraping.mode'    => 'block',
    ]);

    $request = Illuminate\Http\Request::create('/tests', 'GET', server: [
        'HTTP_USER_AGENT' => 'python-requests/2.31.0',
    ]);

    expect(app(ScrapingGuard::class)->inspect($request))->not->toBeNull();
});

it('laisse passer un navigateur ordinaire', function () {
    config(['protection.scraping.enabled' => true]);

    $request = Illuminate\Http\Request::create('/tests', 'GET', server: [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
    ]);

    expect(app(ScrapingGuard::class)->inspect($request))->toBeNull();
});

it('bloque une cadence de consultation impossible pour un humain', function () {
    config([
        'protection.scraping.enabled'        => true,
        'protection.scraping.max_hits'       => 5,
        'protection.scraping.window_minutes' => 10,
    ]);

    $guard   = app(ScrapingGuard::class);
    $request = Illuminate\Http\Request::create('/tests', 'GET', server: [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Chrome/120.0',
        'REMOTE_ADDR'     => '203.0.113.42',
    ]);

    // Les cinq premières lectures restent sous le plafond.
    foreach (range(1, 5) as $i) {
        expect($guard->inspect($request))->toBeNull();
    }

    expect($guard->inspect($request))->not->toBeNull()
        ->and($guard->isBlocked($request))->toBeTrue()
        ->and(ProtectionAlert::where('type', ProtectionAlert::TYPE_SCRAPING)->count())->toBe(1);
});

it('n\'inspecte rien quand l\'anti-scraping est désactivé', function () {
    config(['protection.scraping.enabled' => false]);

    $request = Illuminate\Http\Request::create('/tests', 'GET', server: [
        'HTTP_USER_AGENT' => 'curl/8.0',
    ]);

    expect(app(ScrapingGuard::class)->inspect($request))->toBeNull();
});

// ─── 3. Partage de comptes ────────────────────────────────────────────────────

it('alerte quand un compte professionnel dépasse le plafond d\'appareils', function () {
    Role::findOrCreate('professional', 'web');

    config([
        'protection.sharing.enabled'                => true,
        'protection.sharing.max_devices'            => 2,
        'protection.sharing.touch_interval_minutes' => 0,
    ]);

    $user = User::factory()->create();
    $user->assignRole('professional');

    $guard = app(DeviceGuard::class);

    // Trois navigateurs distincts sur le même compte : au-delà du plafond,
    // le troisième déclenche l'anomalie.
    $agents = ['Chrome/120.0', 'Firefox/121.0', 'Safari/17.0'];
    $anomaly = null;

    foreach ($agents as $i => $agent) {
        $request = Illuminate\Http\Request::create('/dashboard', 'GET', server: [
            'HTTP_USER_AGENT' => "Mozilla/5.0 (Windows NT 10.0) {$agent}",
            'REMOTE_ADDR'     => '198.51.100.' . ($i + 1),
        ]);
        $request->setUserResolver(fn () => $user);

        $anomaly = $guard->inspect($request);
    }

    expect(UserDevice::where('user_id', $user->id)->count())->toBe(3)
        ->and($anomaly)->not->toBeNull()
        ->and(ProtectionAlert::where('type', ProtectionAlert::TYPE_SHARING)->count())->toBe(1);
});

it('ne surveille pas les comptes candidats', function () {
    Role::findOrCreate('candidate', 'web');

    config([
        'protection.sharing.enabled'     => true,
        'protection.sharing.max_devices' => 1,
    ]);

    $user = User::factory()->create();
    $user->assignRole('candidate');

    $request = Illuminate\Http\Request::create('/dashboard', 'GET', server: [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Chrome/120.0',
    ]);
    $request->setUserResolver(fn () => $user);

    expect(app(DeviceGuard::class)->inspect($request))->toBeNull()
        ->and(UserDevice::count())->toBe(0);
});

// ─── 4. Tatouage des PDF ──────────────────────────────────────────────────────

it('produit un code de traçage stable et propre à chaque compte', function () {
    $watermark = app(DocumentWatermark::class);

    $alice = User::factory()->create();
    $bob   = User::factory()->create();

    $codeAlice = $watermark->code($alice, 'results:42');

    expect($codeAlice)->toMatch('/^[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}$/')
        // Stable : le même compte et le même document donnent le même code.
        ->and($watermark->code($alice, 'results:42'))->toBe($codeAlice)
        // Distinct : un autre compte, ou un autre document, donne autre chose.
        ->and($watermark->code($bob, 'results:42'))->not->toBe($codeAlice)
        ->and($watermark->code($alice, 'results:43'))->not->toBe($codeAlice);
});

it('remonte du code de traçage au compte à l\'origine de la fuite', function () {
    $watermark = app(DocumentWatermark::class);

    User::factory()->count(3)->create();
    $leaker = User::factory()->create();

    $code  = $watermark->code($leaker, 'grimoire:7');
    $found = $watermark->resolve($code, 'grimoire:7');

    expect($found?->id)->toBe($leaker->id)
        ->and(ProtectionAlert::where('type', ProtectionAlert::TYPE_PDF_LEAK)->count())->toBe(1);
});

it('n\'expose pas de tatouage quand le traçage est désactivé', function () {
    config(['protection.watermark.enabled' => false]);

    expect(app(DocumentWatermark::class)->stamp(User::factory()->create(), 'results:1'))
        ->toBeNull();
});
