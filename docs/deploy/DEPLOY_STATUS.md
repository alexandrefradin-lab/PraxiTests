# PraxiQuest — État du déploiement OVH

**Date :** 2026-06-18  
**URL cible :** https://praxiquest.decisionpro.fr  
**Stack :** Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS + MySQL

---

## Accès SSH

```
Host : ssh.cluster121.hosting.ovh.net
Port : 22
Login : decisiv
```

> Note : un script de déploiement tourne automatiquement à chaque connexion SSH (`git pull` + `composer install`). Toute modification de fichier trackée par git sera **écrasée** à la prochaine session SSH — il faut committer + pusher pour que les changements soient permanents.

---

## Architecture OVH (important)

- Web root : `~/praxiquest/public` (= `/home/decisiv/praxiquest/public`)
- `/home/decisiv` est un **symlink** vers `/homez.10002/decisiv`
- Reverse proxy : **openresty (nginx)** — ne peut PAS suivre les symlinks pour les fichiers statiques → timeout 504
- Backend PHP : **PHP-FPM 8.2** (pas de mod_php / Apache)
- Conséquence : `.htaccess` n'a **aucun effet** sur le routage des fichiers statiques — tout passe par PHP

---

## Problèmes résolus ✅

| Problème | Correctif appliqué |
|---|---|
| Redirect vers install.php | Créé `storage/app/.installed` |
| `storage/framework/` manquant | `mkdir -p sessions views cache/data` + chmod 755 |
| `bootstrap/cache/` manquant | mkdir + chmod 755 |
| APP_KEY absent | `php artisan key:generate --force` |
| `.htaccess` absent dans `public/` | Créé le fichier standard Laravel |
| Blade cache path error | `php artisan config:clear view:clear cache:clear` |

---

## Problème en cours ⚠️

### Assets JS/CSS servis avec `text/html` au lieu de `application/javascript`

**Cause confirmée :** nginx OVH ne peut pas résoudre le symlink `/home/decisiv` pour servir les fichiers statiques → timeout 504. Il passe donc TOUT à PHP. Laravel reçoit la requête `/build/assets/app-C0tjsYV2.js` et répond avec le HTML de la SPA (route catch-all).

**Correctif préparé — PAS ENCORE APPLIQUÉ :**

Modifier `public/index.php` pour intercepter les requêtes `/build/` et servir les fichiers avec le bon MIME type, **avant** que Laravel ne démarre.

Le fichier local `public/index.php` a déjà été modifié. Il faut :

1. **Sur le serveur SSH (session en cours)** — coller ce `cat` :

```bash
cat > ~/praxiquest/public/index.php << 'PHPEOF'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ── Serve static build assets ─────────────────────────────────────────────────
// OVH shared hosting (openresty) cannot follow symlinks for static files,
// so every request reaches PHP. We intercept /build/ paths here and stream
// the file directly with the proper Content-Type — no Laravel overhead.
(function () {
    $uri  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    if (strncmp($uri, '/build/', 7) !== 0) return;
    $file = __DIR__ . $uri;
    if (!is_file($file)) return;
    static $mime = [
        'js'    => 'application/javascript; charset=utf-8',
        'mjs'   => 'application/javascript; charset=utf-8',
        'css'   => 'text/css; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'map'   => 'application/json',
        'json'  => 'application/json',
    ];
    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=31536000, immutable');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
})();
// ─────────────────────────────────────────────────────────────────────────────

// Détection install
if (!file_exists(__DIR__ . '/../.env') || !file_exists(__DIR__ . '/../storage/app/.installed')) {
    if (!str_ends_with($_SERVER['REQUEST_URI'] ?? '', 'install.php')) {
        header('Location: install.php');
        exit;
    }
}

// Maintenance mode
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
PHPEOF
echo "Done: $?"
```

2. **Commit + push depuis la machine locale** (pour survivre aux prochains git pull) :

```bash
git add public/index.php
git commit -m "fix: serve /build/ assets via PHP on OVH (nginx symlink issue)"
git push
```

---

## Fichiers de diagnostic à supprimer

```bash
rm ~/praxiquest/public/dbg.php
rm ~/praxiquest/public/build/test.txt
```

---

## Tâches restantes après le fix JS

- [ ] Vérifier que le site charge correctement sur https://praxiquest.decisionpro.fr
- [ ] Vérifier la connexion / inscription utilisateur
- [ ] Fix **TestNormsSeeder** — `Undefined array key 0` à la ligne 41 de `TestNormsSeeder.php` (non-critique, les migrations ont réussi)
- [ ] Fix **SEC-07** — XSS dans les emails de campagne : appliquer HTMLPurifier sur `{!! $html !!}`

---

## Contenu de `~/praxiquest/.env` (référence)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://praxiquest.decisionpro.fr
APP_KEY=<généré par key:generate>
DB_CONNECTION=mysql
DB_HOST=decisivpraxitest.mysql.db
DB_PORT=3306
DB_DATABASE=decisivpraxitest
DB_USERNAME=decisivpraxitest
DB_PASSWORD=M5oi3Z27w
AI_DEFAULT_DRIVER=anthropic
ANTHROPIC_API_KEY=sk-ant-api03-...
PRAXIQUEST_ADMIN_EMAIL=alexandre.fradin@gmail.com
PRAXIQUEST_ADMIN_PASSWORD=Admin@PraxiQ2026!
```

---

## Contenu de `~/praxiquest/public/.htaccess` (actuel)

```apache
Options +FollowSymLinks -MultiViews

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

> Note : ce `.htaccess` est sans effet pour le routage statique (nginx bypass Apache). Il reste utile si Apache est jamais impliqué pour d'autres requêtes PHP.
