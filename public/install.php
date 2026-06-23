<?php
/**
 * PraxiQuest — Installeur web standalone v2
 * Multi-step AJAX pour contourner le timeout OVH (30s)
 */
declare(strict_types=1);
set_time_limit(120);
ignore_user_abort(true);

const PT_VERSION      = '1.0.0-alpha';
const PT_MIN_PHP      = '8.2.0';
const PT_REQUIRED_EXT = ['pdo','mbstring','openssl','fileinfo','json','tokenizer','xml','ctype','curl','bcmath'];

$root        = dirname(__DIR__);
$installFlag = $root . '/storage/app/.installed';
$envPath     = $root . '/.env';
$envExample  = $root . '/.env.example';
$vendorAuto  = $root . '/vendor/autoload.php';
$vendorReady = file_exists($vendorAuto);

/* ═══════════════ GUARD DEJA INSTALLE (prioritaire) ═══════════════ */
/*
 * Double garde :
 *  1. Le flag storage/app/.installed (posé en fin d'installation).
 *  2. Un .env déjà configuré (APP_KEY non vide) — défense en profondeur :
 *     même si le flag est supprimé par accident ou par une autre faille,
 *     une application déjà configurée NE PEUT PLUS être ré-installée
 *     (donc plus de DROP TABLE possible).
 * Un override explicite reste possible pour une ré-installation volontaire
 * via la variable d'environnement serveur PT_ALLOW_REINSTALL=1.
 */
$allowReinstall = getenv('PT_ALLOW_REINSTALL') === '1';

$envConfigured = false;
if (!$allowReinstall && is_readable($envPath)) {
    $envContents = (string) file_get_contents($envPath);
    if (preg_match('/^\s*APP_KEY\s*=\s*\S+/m', $envContents)) {
        // APP_KEY présent et non vide => application déjà installée
        $envConfigured = (bool) preg_match('/^\s*APP_KEY\s*=\s*base64:\S+/m', $envContents)
            || (bool) preg_match('/^\s*APP_KEY\s*=\s*"?[^\s"]{16,}/m', $envContents);
    }
}

if (!$allowReinstall && (file_exists($installFlag) || $envConfigured)) {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => 'Application already installed.']);
    } else {
        http_response_code(403);
        echo '<h1>Already installed</h1><p>This application is already configured.</p>';
    }
    exit;
}

/* ═══════════════ AJAX ENDPOINT ═══════════════ */
if (isset($_GET['ajax'])) {
    // Capturer toute sortie parasite (notices PHP, warnings, etc.) qui casserait le JSON
    ob_start();
    header('Content-Type: application/json; charset=utf-8');
    $step = (int)($_GET['ajax'] ?? 0);

    // Étape 0 : juste confirmer que le .env est prêt
    if ($step === 0) {
        ob_end_clean();
        echo json_encode(['ok' => file_exists($root . '/.env')]);
        exit;
    }

    // Bootstrap Laravel depuis le .env déjà écrit
    try {
        if (!$vendorReady) throw new \RuntimeException("vendor/autoload.php introuvable. Verifiez que vendor/ est bien uploade.");
        if (!file_exists($root . '/.env')) throw new \RuntimeException(".env introuvable. Relancez le formulaire.");
        require_once $vendorAuto;
        $app    = require $root . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    } catch (\Throwable $e) {
        ob_end_clean();
        echo json_encode(['ok' => false, 'log' => [['ok' => false, 'label' => 'Bootstrap Laravel', 'msg' => $e->getMessage() . ' — ' . $e->getFile() . ':' . $e->getLine()]]]);
        exit;
    }

    $run = function(string $cmd, array $params = []) use ($kernel): array {
        try {
            $out  = new \Symfony\Component\Console\Output\BufferedOutput();
            $code = $kernel->call($cmd, $params, $out);
            return ['ok' => $code === 0, 'label' => $cmd, 'msg' => trim($out->fetch())];
        } catch (\Throwable $e) {
            return ['ok' => false, 'label' => $cmd, 'msg' => $e->getMessage()];
        }
    };

    $log = [];
    ob_end_clean(); // vider les éventuels echo du bootstrap

    switch ($step) {

        case 1: // Wipe + Migrate
            try {
                $db = app('db');
                $db->statement('SET FOREIGN_KEY_CHECKS=0');
                foreach ($db->select('SHOW TABLES') as $t) {
                    $tn = array_values((array)$t)[0];
                    $db->statement("DROP TABLE IF EXISTS `{$tn}`");
                }
                $db->statement('SET FOREIGN_KEY_CHECKS=1');
                $log[] = ['ok' => true, 'label' => 'db:wipe', 'msg' => 'Tables supprimees.'];
            } catch (\Throwable $e) {
                $log[] = ['ok' => false, 'label' => 'db:wipe', 'msg' => $e->getMessage()];
                echo json_encode(['ok' => false, 'log' => $log]); exit;
            }
            $r = $run('migrate', ['--force' => true]);
            $log[] = $r;
            echo json_encode(['ok' => $r['ok'], 'log' => $log]);
            break;

        case 2: // Seed
            $r = $run('db:seed', ['--force' => true]);
            $log[] = $r;
            echo json_encode(['ok' => $r['ok'], 'log' => $log]);
            break;

        case 3: // Plugins + storage:link + lock
            try {
                $log[] = $run('storage:link');
            } catch (\Throwable $e) {
                $log[] = ['ok' => true, 'label' => 'storage:link', 'msg' => 'Deja lie.'];
            }
            $r2 = $run('praxiquest:plugins:discover', ['--sync' => true]);
            $log[] = $r2;
            if ($r2['ok']) {
                foreach (['praximet','praxivaleurs','praxicare','praxiemo','praximum'] as $slug) {
                    $log[] = $run('praxiquest:plugins:activate', ['slug' => $slug]);
                }
            }
            @mkdir(dirname($installFlag), 0755, true);
            file_put_contents($installFlag, json_encode(['version' => PT_VERSION, 'installed_at' => date('c')], JSON_PRETTY_PRINT));
            $log[] = ['ok' => true, 'label' => 'Verrouillage', 'msg' => 'Installeur verrouille.'];
            echo json_encode(['ok' => true, 'log' => $log]);
            break;

        default:
            echo json_encode(['ok' => false, 'log' => [['ok' => false, 'label' => 'Erreur', 'msg' => 'Etape inconnue.']]]);
    }
    exit;
}

/* ═══════════════ VERIFICATIONS ═══════════════ */
$phpOk  = version_compare(PHP_VERSION, PT_MIN_PHP, '>=');
$extOk  = !in_array(false, array_map('extension_loaded', PT_REQUIRED_EXT), true);
$permOk = is_writable($root . '/storage') && is_writable($root . '/bootstrap/cache');
$allOk  = $phpOk && $extOk && $permOk && $vendorReady;

/* ═══════════════ ECRITURE .ENV ═══════════════ */
function pt_write_env(string $envPath, string $example, array $p): void {
    // Nettoyer toutes les valeurs POST pour éviter l'injection de lignes dans le .env
    $clean = static function(string $v): string {
        return str_replace(["\n", "\r", "\0"], '', $v);
    };

    $env = $example;
    $key = 'base64:' . base64_encode(random_bytes(32));
    $set = function(string $name, string $value) use (&$env): void {
        if (preg_match('/^' . preg_quote($name, '/') . '=.*$/m', $env))
            $env = preg_replace('/^' . preg_quote($name, '/') . '=.*$/m', "{$name}={$value}", $env);
        else
            $env .= "\n{$name}={$value}";
    };
    $set('APP_KEY',    $key);
    $set('APP_ENV',    'production');
    $set('APP_DEBUG',  'false');
    $set('APP_NAME',   '"' . str_replace('"', "'", $clean($p['app_name'] ?? 'PraxiQuest')) . '"');
    $set('APP_URL',    rtrim($clean($p['app_url'] ?? 'http://localhost'), '/'));
    $set('DB_CONNECTION', $clean($p['db_connection'] ?? 'mysql'));
    $set('DB_HOST',       $clean($p['db_host']       ?? '127.0.0.1'));
    $set('DB_PORT',       $clean($p['db_port']       ?? '3306'));
    $set('DB_DATABASE',   $clean($p['db_database']   ?? ''));
    $set('DB_USERNAME',   $clean($p['db_username']   ?? ''));
    $set('DB_PASSWORD',   $clean($p['db_password']   ?? ''));
    if (!empty($p['MAIL_HOST']))         $set('MAIL_HOST',         $clean($p['MAIL_HOST']));
    if (!empty($p['MAIL_PORT']))         $set('MAIL_PORT',         $clean($p['MAIL_PORT']));
    if (!empty($p['MAIL_USERNAME']))     $set('MAIL_USERNAME',     $clean($p['MAIL_USERNAME']));
    if (!empty($p['MAIL_PASSWORD']))     $set('MAIL_PASSWORD',     $clean($p['MAIL_PASSWORD']));
    if (!empty($p['MAIL_ENCRYPTION']))   $set('MAIL_ENCRYPTION',   $clean($p['MAIL_ENCRYPTION']));
    if (!empty($p['MAIL_FROM_ADDRESS'])) $set('MAIL_FROM_ADDRESS', $clean($p['MAIL_FROM_ADDRESS']));
    $set('PRAXIQUEST_ADMIN_EMAIL',    $clean($p['admin_email']    ?? ''));
    $set('PRAXIQUEST_ADMIN_PASSWORD', $clean($p['admin_password'] ?? ''));
    $set('PRAXIQUEST_ADMIN_NAME',     '"' . $clean($p['admin_name'] ?? 'Administrateur') . '"');
    file_put_contents($envPath, $env);
}

/* ═══════════════ TRAITEMENT POST (ecriture .env uniquement) ═══════════════ */
$step    = 'form';
$fatal   = null;
$envDone = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'prepare') {
    $step = 'progress';
    $p    = $_POST;
    if (!$vendorReady) $fatal = "Dossier vendor/ absent.";
    if (!$fatal && (empty($p['db_database']) || empty($p['db_username']))) $fatal = "Nom de base et utilisateur DB requis.";
    if (!$fatal && (empty($p['admin_email']) || !filter_var($p['admin_email'], FILTER_VALIDATE_EMAIL))) $fatal = "Email admin invalide.";
    if (!$fatal && (empty($p['admin_password']) || strlen($p['admin_password']) < 8)) $fatal = "Mot de passe admin : 8 caracteres minimum.";
    if (!$fatal && empty($p['app_url'])) $fatal = "URL du site requise.";
    if (!$fatal) {
        // Test connexion DB
        try {
            $dsn = "mysql:host={$p['db_host']};port={$p['db_port']};dbname={$p['db_database']};charset=utf8mb4";
            new PDO($dsn, $p['db_username'], $p['db_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (\PDOException $e) {
            $fatal = "Connexion DB impossible : " . $e->getMessage();
        }
    }
    if (!$fatal) {
        pt_write_env($envPath, file_get_contents($envExample), $p);
        $envDone = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installation — PraxiQuest</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:2.5rem 1rem}
.shell{width:100%;max-width:700px}
.card{background:#fff;border-radius:14px;box-shadow:0 4px 28px rgba(0,0,0,.09);overflow:hidden}
.hero{background:linear-gradient(135deg,#4f46e5 0%,#10b981 100%);padding:2rem 2.5rem;color:#fff}
.hero-logo{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;background:rgba(255,255,255,.2);border-radius:10px;font-size:15px;font-weight:800;margin-bottom:14px}
.hero h1{font-size:24px;font-weight:700}
.hero-sub{font-size:13px;opacity:.8;margin-top:3px}
.hero-ver{display:inline-block;font-size:11px;font-weight:600;background:rgba(255,255,255,.18);border-radius:20px;padding:2px 10px;margin-top:8px}
.body{padding:2rem 2.5rem}
.section{border-top:1px solid #f1f5f9;padding-top:1.4rem;margin-top:1.4rem}
.section:first-child{border-top:none;margin-top:0;padding-top:0}
.section-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:1rem}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}
@media(max-width:520px){.grid2{grid-template-columns:1fr}}
.field{display:flex;flex-direction:column;gap:5px}
label{font-size:13px;font-weight:600;color:#334155}
input,select{width:100%;padding:.6rem .85rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;font-family:inherit;background:#fafafa;color:#0f172a}
input:focus,select:focus{outline:none;border-color:#4f46e5;background:#fff}
.hint{font-size:11px;color:#94a3b8}
.btn{display:inline-block;background:#4f46e5;color:#fff;padding:.85rem 1.5rem;border:0;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;width:100%;text-align:center}
.btn:disabled{opacity:.5;cursor:not-allowed}
.banner{padding:.85rem 1rem;border-radius:9px;font-size:13px;line-height:1.5;margin-bottom:1rem}
.banner-warn{background:#fffbeb;color:#92400e;border:1px solid #fcd34d}
.banner-error{background:#fef2f2;color:#991b1b;border:1px solid #fca5a5}
.banner-success{background:#ecfdf5;color:#065f46;border:1px solid #6ee7b7}
.checks{display:flex;flex-direction:column;gap:5px;margin:1rem 0}
.check-item{display:flex;justify-content:space-between;padding:.55rem .85rem;border-radius:7px;font-size:13px}
.check-item.ok{background:#f0fdf4;color:#166534}
.check-item.ko{background:#fef2f2;color:#991b1b}
.result-list{display:flex;flex-direction:column;gap:6px;margin:1rem 0}
.result-item{display:flex;align-items:flex-start;gap:.75rem;padding:.7rem .9rem;border-radius:8px;font-size:13px}
.result-item.ok{background:#f0fdf4;color:#15803d}
.result-item.ko{background:#fef2f2;color:#b91c1c}
.result-label{font-weight:600;min-width:150px}
.result-msg{opacity:.85;word-break:break-word}
.progress-step{padding:.6rem 1rem;border-radius:8px;font-size:13px;color:#475569;font-style:italic;background:#f8fafc;margin-bottom:4px}
.footer{text-align:center;color:#94a3b8;font-size:12px;margin-top:1.2rem}
code{font-family:monospace;font-size:12px;background:#f1f5f9;padding:1px 5px;border-radius:4px}
</style>
</head>
<body>
<div class="shell">
<div class="card">
<div class="hero">
  <div class="hero-logo">PT</div>
  <h1>PraxiQuest</h1>
  <p class="hero-sub">Assistant d'installation</p>
  <span class="hero-ver">v<?= PT_VERSION ?></span>
</div>
<div class="body">

<?php if ($step === 'form'): ?>

<!-- VERIFICATIONS -->
<div class="section">
  <div class="section-title">Verifications systeme</div>
  <div class="checks">
    <div class="check-item <?= $phpOk ? 'ok' : 'ko' ?>"><span>PHP <?= PT_MIN_PHP ?>+</span><span><?= $phpOk ? '&#10003; PHP ' . PHP_VERSION : '&#10007; PHP ' . PHP_VERSION ?></span></div>
    <div class="check-item <?= $extOk ? 'ok' : 'ko' ?>"><span>Extensions requises</span><span><?= $extOk ? '&#10003; Toutes presentes' : '&#10007; Manquantes' ?></span></div>
    <div class="check-item <?= $permOk ? 'ok' : 'ko' ?>"><span>Permissions storage/</span><span><?= $permOk ? '&#10003; OK' : '&#10007; Non accessible' ?></span></div>
    <div class="check-item <?= $vendorReady ? 'ok' : 'ko' ?>"><span>vendor/ present</span><span><?= $vendorReady ? '&#10003;' : '&#10007; Absent' ?></span></div>
  </div>
</div>

<!-- FORMULAIRE -->
<form id="install-form" method="post">
  <input type="hidden" name="action" value="prepare">

  <div class="section">
    <div class="section-title">Base de donnees</div>
    <div class="grid2">
      <div class="field"><label>Hote</label><input type="text" name="db_host" value="127.0.0.1" required><span class="hint">Sur OVH : <code>XXXXX.mysql.db</code></span></div>
      <div class="field"><label>Port</label><input type="text" name="db_port" value="3306" required></div>
    </div>
    <div class="field" style="margin-top:.8rem"><label>Nom de la base</label><input type="text" name="db_database" required><input type="hidden" name="db_connection" value="mysql"></div>
    <div class="grid2" style="margin-top:.8rem">
      <div class="field"><label>Utilisateur</label><input type="text" name="db_username" required></div>
      <div class="field"><label>Mot de passe</label><input type="password" name="db_password"></div>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Compte administrateur</div>
    <div class="field" style="margin-bottom:.8rem"><label>Nom complet</label><input type="text" name="admin_name" placeholder="Alexandre Fradin" required></div>
    <div class="grid2">
      <div class="field"><label>Email</label><input type="email" name="admin_email" required></div>
      <div class="field"><label>Mot de passe (min 8)</label><input type="password" name="admin_password" minlength="8" required></div>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Parametres du site</div>
    <div class="grid2">
      <div class="field"><label>Nom plateforme</label><input type="text" name="app_name" value="PraxiQuest" required></div>
      <div class="field"><label>URL publique</label><input type="url" name="app_url" placeholder="https://www.decisionpro.fr/PraxiQuest/public" required><span class="hint">Sans slash final</span></div>
    </div>
  </div>

  <div class="section">
    <button type="submit" class="btn" >Lancer l'installation &rarr;</button>
  </div>
</form>

<?php elseif ($step === 'progress'): ?>

<?php if ($fatal): ?>
  <div class="banner banner-error">&#10060; <?= htmlspecialchars($fatal) ?></div>
  <a href="install.php" class="btn" style="margin-top:1rem;display:inline-block">&#8592; Retour</a>
<?php else: ?>
  <div class="banner banner-success">&#10003; Configuration validee. Installation en cours...</div>
  <div id="log-list" class="result-list"></div>
  <div id="status-msg" class="progress-step">Demarrage...</div>
  <div id="final-msg" style="display:none"></div>

  <script>
  (function() {
    var logList = document.getElementById('log-list');
    var statusMsg = document.getElementById('status-msg');
    var finalMsg = document.getElementById('final-msg');

    function addLog(ok, label, msg) {
      var d = document.createElement('div');
      d.className = 'result-item ' + (ok ? 'ok' : 'ko');
      d.innerHTML = '<span>' + (ok ? '&#10003;' : '&#10007;') + '</span>'
        + '<span class="result-label">' + label + '</span>'
        + '<span class="result-msg">' + msg + '</span>';
      logList.appendChild(d);
    }

    function setStatus(msg) {
      statusMsg.textContent = msg;
    }

    function runStep(n, label) {
      setStatus(label);
      return fetch('install.php?ajax=' + n, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(function(r) {
        return r.text().then(function(txt) {
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ' — ' + txt.replace(/<[^>]*>/g,'').substring(0, 500));
          }
          try {
            return JSON.parse(txt);
          } catch(e) {
            throw new Error('Reponse non-JSON (PHP error?) : ' + txt.replace(/<[^>]*>/g,'').substring(0, 500));
          }
        });
      })
      .then(function(data) {
        if (data.log) {
          data.log.forEach(function(l) { addLog(l.ok, l.label, l.msg); });
        }
        return data.ok;
      });
    }

    runStep(1, 'Etape 1/3 : Creation des tables...')
    .then(function(ok) {
      if (!ok) { setStatus('Erreur a l etape 1.'); return false; }
      return runStep(2, 'Etape 2/3 : Chargement des donnees...');
    })
    .then(function(ok) {
      if (ok === false) return;
      if (!ok) { setStatus('Erreur a l etape 2.'); return; }
      return runStep(3, 'Etape 3/3 : Activation des plugins...');
    })
    .then(function(ok) {
      if (ok === undefined || ok === false) return;
      statusMsg.style.display = 'none';
      finalMsg.style.display = 'block';
      if (ok) {
        finalMsg.innerHTML = '<div class="banner banner-success">&#10003; <strong>PraxiQuest installe avec succes !</strong></div>'
          + '<a href="/" style="display:inline-block;margin-top:1rem;background:#4f46e5;color:#fff;padding:.8rem 1.5rem;border-radius:9px;font-weight:700;text-decoration:none">Ouvrir l\'application &rarr;</a>';
      } else {
        finalMsg.innerHTML = '<div class="banner banner-error">&#10007; <strong>Installation interrompue.</strong> Consultez les messages ci-dessus, corrigez puis relancez.</div>'
          + '<a href="install.php" style="display:inline-block;margin-top:1rem;background:#64748b;color:#fff;padding:.8rem 1.5rem;border-radius:9px;font-weight:700;text-decoration:none">&#8592; Retour</a>';
      }
    });
  })();
  </script>

<?php endif; ?>
<?php endif; ?>

</div>
</div>
</div>
</body>
</html>
