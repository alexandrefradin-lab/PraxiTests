<?php
/**
 * PraxiTests — Installeur web standalone.
 *
 * UX : 1 formulaire tout-en-un → 1 page résultats (style DecisionPro).
 * Aucune CLI requise : boot Laravel programmatiquement.
 * Auto-verrouille après installation (storage/app/.installed).
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

const PT_VERSION      = '1.0.0-alpha';
const PT_MIN_PHP      = '8.2.0';
const PT_REQUIRED_EXT = ['pdo', 'mbstring', 'openssl', 'fileinfo', 'json', 'tokenizer', 'xml', 'ctype', 'curl', 'bcmath'];

$root        = dirname(__DIR__);
$installFlag = $root . '/storage/app/.installed';
$envPath     = $root . '/.env';
$envExample  = $root . '/.env.example';
$vendorAuto  = $root . '/vendor/autoload.php';
$vendorReady = file_exists($vendorAuto);

/* ───────────────────────── GUARD : déjà installé ───────────────────────── */
if (file_exists($installFlag) && !isset($_GET['force'])) {
    http_response_code(403);
    pt_already_installed();
    exit;
}

/* ─────────────────────────────── FONCTIONS ─────────────────────────────── */

/**
 * Teste la connexion DB, crée la base si absente.
 * Retourne ['ok' => bool, 'error' => ?string, 'created' => bool].
 */
function pt_test_or_create_db(array $cfg): array
{
    $driver = $cfg['db_connection'] ?? 'mysql';
    $host   = $cfg['db_host']       ?? '127.0.0.1';
    $port   = $cfg['db_port']       ?? '3306';
    $name   = $cfg['db_database']   ?? '';
    $user   = $cfg['db_username']   ?? '';
    $pass   = $cfg['db_password']   ?? '';

    if ($driver === 'sqlite') {
        $path = $name ?: dirname(__DIR__) . '/database/database.sqlite';
        if (!file_exists($path)) {
            @touch($path);
            if (!file_exists($path)) {
                return ['ok' => false, 'error' => "Impossible de créer le fichier SQLite : {$path}", 'created' => false];
            }
            return ['ok' => true, 'error' => null, 'created' => true];
        }
        return ['ok' => true, 'error' => null, 'created' => false];
    }

    // Tentative 1 : connexion avec la DB (cas normal)
    try {
        $dsn = "{$driver}:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return ['ok' => true, 'error' => null, 'created' => false];
    } catch (PDOException $e1) {
        $msg1 = $e1->getMessage();
    }

    // Tentative 2 : connexion serveur seule + CREATE DATABASE
    try {
        $dsnServer = "{$driver}:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsnServer, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '', $name);
        if ($safeName !== $name || $safeName === '') {
            return ['ok' => false, 'error' => "Nom de base invalide : « {$name} » (lettres, chiffres, _ et - uniquement).", 'created' => false];
        }

        if ($driver === 'pgsql') {
            $exists = $pdo->query("SELECT 1 FROM pg_database WHERE datname = " . $pdo->quote($safeName))->fetchColumn();
            if (!$exists) $pdo->exec("CREATE DATABASE \"{$safeName}\" ENCODING 'UTF8'");
        } else {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        $dsn = "{$driver}:host={$host};port={$port};dbname={$safeName};charset=utf8mb4";
        new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return ['ok' => true, 'error' => null, 'created' => true];

    } catch (PDOException $e2) {
        $hint = '';
        $m2 = $e2->getMessage();
        if (str_contains($m2, 'Access denied'))
            $hint = " L'utilisateur n'a pas les droits CREATE DATABASE. Crée la base manuellement dans OVH Manager.";
        elseif (str_contains($m2, 'Connection refused') || str_contains($m2, 'getaddrinfo'))
            $hint = " Vérifie l'hôte. Sur OVH il ressemble à « XXXXX.mysql.db », jamais « localhost ».";

        return [
            'ok'      => false,
            'error'   => "Connexion impossible.\n\nErreur avec DB : {$msg1}\nErreur sans DB : {$m2}{$hint}",
            'created' => false,
        ];
    }
}

/** Écrit le .env complet à partir du POST. */
function pt_write_env(string $envPath, string $example, array $p): void
{
    $env = $example;
    $key = 'base64:' . base64_encode(random_bytes(32));

    $set = function (string $name, string $value) use (&$env): void {
        if (preg_match('/^' . preg_quote($name, '/') . '=.*$/m', $env))
            $env = preg_replace('/^' . preg_quote($name, '/') . '=.*$/m', "{$name}={$value}", $env);
        else
            $env .= "\n{$name}={$value}";
    };

    $set('APP_KEY',    $key);
    $set('APP_ENV',    'production');
    $set('APP_DEBUG',  'false');
    $set('APP_NAME',   '"' . str_replace('"', "'", $p['app_name'] ?? 'PraxiTests') . '"');
    $set('APP_URL',    rtrim($p['app_url'] ?? 'http://localhost', '/'));

    $set('DB_CONNECTION', $p['db_connection'] ?? 'mysql');
    $set('DB_HOST',       $p['db_host']       ?? '127.0.0.1');
    $set('DB_PORT',       $p['db_port']       ?? '3306');
    $set('DB_DATABASE',   $p['db_database']   ?? '');
    $set('DB_USERNAME',   $p['db_username']   ?? '');
    $set('DB_PASSWORD',   $p['db_password']   ?? '');

    foreach (['MAIL_HOST','MAIL_PORT','MAIL_USERNAME','MAIL_PASSWORD','MAIL_ENCRYPTION','MAIL_FROM_ADDRESS'] as $k)
        if (!empty($p[$k])) $set($k, $p[$k]);

    if (!empty($p['license_key'])) $set('PRAXITESTS_LICENSE_KEY', $p['license_key']);

    $set('PRAXITESTS_ADMIN_EMAIL',    $p['admin_email']    ?? '');
    $set('PRAXITESTS_ADMIN_PASSWORD', $p['admin_password'] ?? '');
    $set('PRAXITESTS_ADMIN_NAME',     '"' . ($p['admin_name'] ?? 'Administrateur') . '"');

    file_put_contents($envPath, $env);
}

/** Lance migrations, seeders, plugins via le kernel Laravel. */
function pt_run_install(string $root, string $vendorAuto): array
{
    $log = [];
    try {
        require_once $vendorAuto;
        $app    = require $root . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        $run = function (string $cmd, array $params = []) use ($kernel, &$log) {
            $out  = new \Symfony\Component\Console\Output\BufferedOutput();
            $code = $kernel->call($cmd, $params, $out);
            $log[] = ['ok' => $code === 0, 'label' => $cmd, 'msg' => trim($out->fetch())];
            if ($code !== 0) throw new \RuntimeException("Échec : {$cmd}");
        };

        try { $run('storage:link'); } catch (\Throwable) { $log[] = ['ok' => true, 'label' => 'storage:link', 'msg' => 'Lien déjà existant, ignoré.']; }
        $run('migrate', ['--force' => true]);
        $run('db:seed',  ['--force' => true]);
        $run('praxitests:plugins:discover', ['--sync' => true]);

        foreach (['praximet','praxivaleurs','praxicare','praxiemo','praximum'] as $slug) {
            try { $run('praxitests:plugins:activate', ['slug' => $slug]); }
            catch (\Throwable $e) { $log[] = ['ok' => false, 'label' => "plugin:{$slug}", 'msg' => $e->getMessage()]; }
        }

        return ['success' => true, 'log' => $log];
    } catch (\Throwable $e) {
        $log[] = ['ok' => false, 'label' => 'Erreur fatale', 'msg' => $e->getMessage()];
        return ['success' => false, 'log' => $log];
    }
}

/* ─────────────────────────── TRAITEMENT POST ───────────────────────────── */

$step    = 'form';   // 'form' | 'result'
$results = [];       // [['ok'=>bool,'label'=>string,'msg'=>string], ...]
$fatal   = null;     // message d'erreur bloquant avant l'install
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'install') {
    $step = 'result';
    $p    = $_POST;

    // 1. Vendor check
    if (!$vendorReady) {
        $fatal = "Le dossier <code>vendor/</code> est absent. Utilise une distribution complète (zip officiel ou <code>make-release</code>).";
    }

    // 2. Validation basique
    if (!$fatal) {
        if (empty($p['db_database']) || empty($p['db_username']))
            $fatal = "Nom de base et utilisateur DB requis.";
        elseif (empty($p['admin_email']) || !filter_var($p['admin_email'], FILTER_VALIDATE_EMAIL))
            $fatal = "Adresse email administrateur invalide.";
        elseif (empty($p['admin_password']) || strlen($p['admin_password']) < 8)
            $fatal = "Mot de passe administrateur : 8 caractères minimum.";
        elseif (empty($p['app_url']))
            $fatal = "L'URL du site est requise.";
    }

    if (!$fatal) {
        // 3. Test / création DB
        $dbResult = pt_test_or_create_db($p);
        if (!$dbResult['ok']) {
            $fatal = nl2br(htmlspecialchars($dbResult['error']));
        } else {
            $dbLabel = $dbResult['created']
                ? "Base « {$p['db_database']} » créée automatiquement."
                : "Connexion à « {$p['db_database']} » établie.";
            $results[] = ['ok' => true, 'label' => 'Base de données', 'msg' => $dbLabel];

            // 4. Écriture .env
            try {
                pt_write_env($envPath, file_get_contents($envExample), $p);
                $results[] = ['ok' => true, 'label' => 'Fichier .env', 'msg' => 'Généré avec clé APP_KEY aléatoire.'];
            } catch (Throwable $e) {
                $fatal = "Impossible d'écrire .env : " . $e->getMessage();
            }
        }
    }

    if (!$fatal) {
        // 5. Install Laravel
        $install = pt_run_install($root, $vendorAuto);
        foreach ($install['log'] as $entry) $results[] = $entry;

        if ($install['success']) {
            // 6. Verrouillage
            @mkdir(dirname($installFlag), 0755, true);
            file_put_contents($installFlag, json_encode([
                'version'      => PT_VERSION,
                'installed_at' => date('c'),
                'admin_email'  => $_POST['admin_email'] ?? '',
            ], JSON_PRETTY_PRINT));
            $results[] = ['ok' => true, 'label' => 'Verrouillage installeur', 'msg' => 'storage/app/.installed créé.'];
            $success = true;
        }
    }
}

/* ─────────────────────────── FONCTIONS HTML ────────────────────────────── */

function pt_already_installed(): void { ?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>PraxiTests — Déjà installé</title><?php pt_styles(); ?></head>
<body><div class="shell"><div class="card">
<div class="hero"><div class="hero-logo">PT</div><h1>PraxiTests</h1><p class="hero-sub">Installeur</p></div>
<div class="body">
<div class="banner banner-warn">⚠ PraxiTests est déjà installé.</div>
<p style="color:#475569;font-size:14px;margin-top:12px">Pour forcer une réinstallation, supprime <code>storage/app/.installed</code> puis recharge, ou ajoute <code>?force=1</code> à l'URL.</p>
<a href="/" class="btn" style="margin-top:20px">Ouvrir l'application →</a>
</div></div>
<p class="footer">PraxiTests v<?= PT_VERSION ?></p>
</div></body></html>
<?php }

function pt_styles(): void { ?>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:2.5rem 1rem}
.shell{width:100%;max-width:700px}
.card{background:#fff;border-radius:14px;box-shadow:0 4px 28px rgba(0,0,0,.09);overflow:hidden}
/* ── HERO ── */
.hero{background:linear-gradient(135deg,#4f46e5 0%,#10b981 100%);padding:2rem 2.5rem;color:#fff}
.hero-logo{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;background:rgba(255,255,255,.2);border-radius:10px;font-size:15px;font-weight:800;letter-spacing:-.5px;margin-bottom:14px}
.hero h1{font-size:24px;font-weight:700;letter-spacing:-.02em}
.hero-sub{font-size:13px;opacity:.8;margin-top:3px}
.hero-ver{display:inline-block;font-size:11px;font-weight:600;background:rgba(255,255,255,.18);border-radius:20px;padding:2px 10px;margin-top:8px}
/* ── BODY ── */
.body{padding:2rem 2.5rem}
/* ── SECTIONS ── */
.section{border-top:1px solid #f1f5f9;padding-top:1.4rem;margin-top:1.4rem}
.section:first-child{border-top:none;margin-top:0;padding-top:0}
.section-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:1rem}
/* ── FORM ── */
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}
@media(max-width:520px){.grid2{grid-template-columns:1fr}}
.field{display:flex;flex-direction:column;gap:5px}
label{font-size:13px;font-weight:600;color:#334155}
label .opt{font-weight:400;color:#94a3b8;font-size:12px}
input,select{width:100%;padding:.6rem .85rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;font-family:inherit;background:#fafafa;color:#0f172a;transition:border-color .15s,box-shadow .15s}
input:focus,select:focus{outline:none;border-color:#4f46e5;background:#fff;box-shadow:0 0 0 3px rgba(79,70,229,.1)}
.hint{font-size:11px;color:#94a3b8;margin-top:1px}
/* ── BOUTON ── */
.btn{display:inline-block;background:#4f46e5;color:#fff;padding:.85rem 1.5rem;border:0;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;transition:background .15s}
.btn:hover{background:#4338ca}
.btn-full{width:100%;text-align:center}
/* ── BANNERS ── */
.banner{padding:.85rem 1rem;border-radius:9px;font-size:13px;line-height:1.5;margin-bottom:1rem}
.banner-warn{background:#fffbeb;color:#92400e;border:1px solid #fcd34d}
.banner-error{background:#fef2f2;color:#991b1b;border:1px solid #fca5a5}
.banner-success{background:#ecfdf5;color:#065f46;border:1px solid #6ee7b7}
/* ── RESULTS ── */
.result-list{display:flex;flex-direction:column;gap:6px;margin:1rem 0}
.result-item{display:flex;align-items:flex-start;gap:.75rem;padding:.7rem .9rem;border-radius:8px;font-size:13px}
.result-item.ok{background:#f0fdf4;color:#15803d}
.result-item.ko{background:#fef2f2;color:#b91c1c}
.result-icon{font-size:15px;flex-shrink:0;margin-top:1px}
.result-label{font-weight:600;min-width:170px;flex-shrink:0}
.result-msg{color:inherit;opacity:.85;word-break:break-word}
/* ── CHECKLIST REQUIREMENTS ── */
.checks{display:flex;flex-direction:column;gap:5px;margin:1rem 0}
.check-item{display:flex;justify-content:space-between;align-items:center;padding:.55rem .85rem;border-radius:7px;font-size:13px}
.check-item.ok{background:#f0fdf4;color:#166534}
.check-item.ko{background:#fef2f2;color:#991b1b}
/* ── SUCCESS / NEXT STEPS ── */
.next-steps{background:#f8fafc;border-radius:10px;padding:1.2rem 1.4rem;margin-top:1.5rem}
.next-steps h3{font-size:13px;font-weight:700;color:#334155;margin-bottom:.75rem}
.next-steps ol{margin-left:1.2rem;color:#475569;font-size:13px;line-height:1.8}
.delete-warn{background:#fff3e0;border:1px solid #fbbf24;border-radius:9px;padding:.85rem 1rem;margin-top:1rem;font-size:13px;color:#92400e}
/* ── FOOTER ── */
.footer{text-align:center;color:#94a3b8;font-size:12px;margin-top:1.2rem}
code{font-family:ui-monospace,monospace;font-size:12px;background:#f1f5f9;padding:1px 5px;border-radius:4px}
</style>
<?php }

/* ═══════════════════════════════ HTML ═══════════════════════════════════ */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installation — PraxiTests</title>
<?php pt_styles(); ?>
</head>
<body>
<div class="shell">
<div class="card">

  <!-- HERO -->
  <div class="hero">
    <div class="hero-logo">PT</div>
    <h1>PraxiTests</h1>
    <p class="hero-sub">Assistant d'installation</p>
    <span class="hero-ver">v<?= PT_VERSION ?></span>
  </div>

  <div class="body">

<?php if ($step === 'form'): ?>

  <!-- ═══ FORMULAIRE ═══ -->

  <?php if (!$vendorReady): ?>
  <div class="banner banner-warn">⚠ <strong>Dossier vendor/ manquant.</strong> Utilise une distribution complète (zip officiel ou <code>make-release</code>). L'installation ne peut pas continuer.</div>
  <?php endif; ?>

  <!-- Prérequis rapides -->
  <?php
    $phpOk  = version_compare(PHP_VERSION, PT_MIN_PHP, '>=');
    $extOk  = !in_array(false, array_map('extension_loaded', PT_REQUIRED_EXT), true);
    $permOk = is_writable($root . '/storage') && is_writable($root . '/bootstrap/cache');
    $allOk  = $phpOk && $extOk && $permOk && $vendorReady;
  ?>
  <div class="section">
    <div class="section-title">Vérifications système</div>
    <div class="checks">
      <div class="check-item <?= $phpOk ? 'ok' : 'ko' ?>"><span>PHP <?= PT_MIN_PHP ?>+</span><span><?= $phpOk ? '✓ PHP ' . PHP_VERSION : '✗ PHP ' . PHP_VERSION ?></span></div>
      <div class="check-item <?= $extOk ? 'ok' : 'ko' ?>"><span>Extensions requises</span><span><?= $extOk ? '✓ Toutes présentes' : '✗ Manquantes : ' . implode(', ', array_filter(PT_REQUIRED_EXT, fn($e) => !extension_loaded($e))) ?></span></div>
      <div class="check-item <?= $permOk ? 'ok' : 'ko' ?>"><span>Permissions storage/ & bootstrap/cache/</span><span><?= $permOk ? '✓ Accessibles en écriture' : '✗ Non accessibles' ?></span></div>
      <div class="check-item <?= $vendorReady ? 'ok' : 'ko' ?>"><span>vendor/ présent</span><span><?= $vendorReady ? '✓' : '✗ Absent' ?></span></div>
    </div>
    <?php if (!$allOk): ?>
    <div class="banner banner-warn" style="margin-top:.5rem">Corrige les points rouges avant de lancer l'installation.</div>
    <?php endif; ?>
  </div>

  <form method="post">
    <input type="hidden" name="action" value="install">

    <!-- ── Base de données ── -->
    <div class="section">
      <div class="section-title">🗄️ Base de données</div>
      <div class="field" style="margin-bottom:.9rem">
        <label>Type de base</label>
        <select name="db_connection">
          <option value="mysql">MySQL / MariaDB</option>
          <option value="pgsql">PostgreSQL</option>
          <option value="sqlite">SQLite</option>
        </select>
      </div>
      <div class="grid2">
        <div class="field">
          <label>Hôte</label>
          <input type="text" name="db_host" value="127.0.0.1" required>
          <span class="hint">Sur OVH : <code>XXXXX.mysql.db</code></span>
        </div>
        <div class="field">
          <label>Port</label>
          <input type="text" name="db_port" value="3306" required>
        </div>
      </div>
      <div class="field" style="margin-top:.8rem">
        <label>Nom de la base</label>
        <input type="text" name="db_database" required pattern="[A-Za-z0-9_\-]+" title="Lettres, chiffres, _ et - uniquement">
        <span class="hint">Sera créée automatiquement si l'utilisateur DB a les droits <code>CREATE DATABASE</code>.</span>
      </div>
      <div class="grid2" style="margin-top:.8rem">
        <div class="field">
          <label>Utilisateur</label>
          <input type="text" name="db_username" required>
        </div>
        <div class="field">
          <label>Mot de passe</label>
          <input type="password" name="db_password">
        </div>
      </div>
    </div>

    <!-- ── Compte administrateur ── -->
    <div class="section">
      <div class="section-title">👤 Compte administrateur</div>
      <div class="field" style="margin-bottom:.8rem">
        <label>Nom complet</label>
        <input type="text" name="admin_name" placeholder="Alexandre Fradin" required>
      </div>
      <div class="grid2">
        <div class="field">
          <label>Email</label>
          <input type="email" name="admin_email" required>
        </div>
        <div class="field">
          <label>Mot de passe <span style="color:#94a3b8;font-weight:400;font-size:12px">(min 8 car.)</span></label>
          <input type="password" name="admin_password" minlength="8" required>
        </div>
      </div>
    </div>

    <!-- ── Site ── -->
    <div class="section">
      <div class="section-title">🌐 Paramètres du site</div>
      <div class="grid2">
        <div class="field">
          <label>Nom de la plateforme</label>
          <input type="text" name="app_name" value="PraxiTests" required>
        </div>
        <div class="field">
          <label>URL publique</label>
          <input type="url" name="app_url" placeholder="https://app.monsite.com" required>
          <span class="hint">Sans slash final. Ex : <code>https://praxitests.decisionpro.fr</code></span>
        </div>
      </div>
    </div>

    <!-- ── Mail ── -->
    <div class="section">
      <div class="section-title">📧 Envoi de mails <span class="opt" style="font-size:11px;color:#94a3b8;text-transform:none;letter-spacing:0">(optionnel, configurable plus tard)</span></div>
      <div class="grid2">
        <div class="field">
          <label>Hôte SMTP <span class="opt">(optionnel)</span></label>
          <input type="text" name="MAIL_HOST" placeholder="smtp.mailgun.org">
        </div>
        <div class="field">
          <label>Port</label>
          <input type="text" name="MAIL_PORT" value="587">
        </div>
      </div>
      <div class="grid2" style="margin-top:.8rem">
        <div class="field">
          <label>Utilisateur SMTP <span class="opt">(optionnel)</span></label>
          <input type="text" name="MAIL_USERNAME">
        </div>
        <div class="field">
          <label>Mot de passe SMTP <span class="opt">(optionnel)</span></label>
          <input type="password" name="MAIL_PASSWORD">
        </div>
      </div>
      <div class="grid2" style="margin-top:.8rem">
        <div class="field">
          <label>Chiffrement</label>
          <select name="MAIL_ENCRYPTION">
            <option value="tls">TLS</option>
            <option value="ssl">SSL</option>
            <option value="">Aucun</option>
          </select>
        </div>
        <div class="field">
          <label>Adresse expéditeur <span class="opt">(optionnel)</span></label>
          <input type="email" name="MAIL_FROM_ADDRESS" placeholder="hello@monsite.com">
        </div>
      </div>
    </div>

    <!-- ── Licence ── -->
    <div class="section">
      <div class="section-title">🔑 Licence <span class="opt" style="font-size:11px;color:#94a3b8;text-transform:none;letter-spacing:0">(optionnel)</span></div>
      <div class="field">
        <label>Clé de licence <span class="opt">(optionnel)</span></label>
        <input type="text" name="license_key" placeholder="PT-XXXX-XXXX-XXXX">
        <span class="hint">Sans clé : mode démo (30 jours). Laisse vide pour continuer.</span>
      </div>
    </div>

    <!-- ── Résumé de ce qui va s'installer ── -->
    <div class="section">
      <div class="banner" style="background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe;margin-bottom:0">
        <strong>Ce que l'installeur va faire :</strong><br>
        Générer <code>.env</code> + clé app • Créer les tables (~14) • Seeder rôles, badges, test démo • Activer 5 plugins (PraxiMet · PraxiValeurs · PraxiCare · PraxiEmo · PraxiMum) • Verrouiller l'installeur.
      </div>
    </div>

    <div class="section" style="border-top:none;padding-top:0">
      <button
        type="submit"
        class="btn btn-full"
        <?= !$allOk ? 'disabled title="Corrige les prérequis en rouge avant de continuer."' : '' ?>
        onclick="this.disabled=true;this.textContent='Installation en cours…'"
      >Lancer l'installation →</button>
    </div>

  </form>

<?php else: /* ═══ RÉSULTATS ═══ */ ?>

  <?php if ($fatal): ?>
    <div class="banner banner-error">❌ <strong>Échec avant installation :</strong><br><?= $fatal ?></div>
    <!-- Retry -->
    <form method="post" style="margin-top:1.5rem">
      <input type="hidden" name="action" value="install">
      <?php foreach ($_POST as $k => $v): ?>
        <?php if ($k !== 'action' && !is_array($v)): ?>
        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
        <?php endif; ?>
      <?php endforeach; ?>
      <a href="install.php" class="btn">← Revenir au formulaire</a>
    </form>

  <?php else: ?>

    <?php if ($success): ?>
    <div class="banner banner-success">✅ <strong>PraxiTests installé avec succès !</strong></div>
    <?php else: ?>
    <div class="banner banner-error">❌ <strong>Installation incomplète</strong> — certaines étapes ont échoué (voir ci-dessous).</div>
    <?php endif; ?>

    <!-- Checklist résultats -->
    <div class="result-list">
      <?php foreach ($results as $r): ?>
      <div class="result-item <?= $r['ok'] ? 'ok' : 'ko' ?>">
        <span class="result-icon"><?= $r['ok'] ? '✅' : '❌' ?></span>
        <span class="result-label"><?= htmlspecialchars($r['label']) ?></span>
        <span class="result-msg"><?= htmlspecialchars($r['msg']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($success): ?>
    <!-- Prochaines étapes -->
    <div class="next-steps">
      <h3>Prochaines étapes</h3>
      <ol>
        <li>Ouvrir l'application et te connecter avec ton compte admin.</li>
        <li>Changer le mot de passe depuis les paramètres de ton profil.</li>
        <li>Configurer ta clé API IA dans <code>.env</code> (ANTHROPIC_API_KEY, OPENAI_API_KEY…) pour activer la synthèse et les 15 métiers.</li>
        <li>Supprimer <code>install.php</code> via FTP/SFTP par mesure de sécurité.</li>
      </ol>
    </div>
    <div class="delete-warn">⚠ <strong>Sécurité :</strong> supprime <code>public/install.php</code> de ton serveur dès que possible. L'accès est déjà verrouillé, mais supprimer le fichier est une précaution supplémentaire recommandée.</div>
    <a href="/" class="btn" style="margin-top:1.5rem;display:inline-block">Ouvrir l'application →</a>

    <?php else: ?>
    <a href="install.php" class="btn" style="margin-top:1rem;display:inline-block">← Revenir au formulaire</a>
    <?php endif; ?>

  <?php endif; ?>

<?php endif; ?>

  </div><!-- /body -->
</div><!-- /card -->
<p class="footer">PraxiTests v<?= PT_VERSION ?> · Installeur</p>
</div><!-- /shell -->
</body>
</html>
