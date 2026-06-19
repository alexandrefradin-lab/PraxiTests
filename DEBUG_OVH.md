# Debug OVH — PraxiQuest 500 Error
**Date :** 19 juin 2026 | **Env :** OVH cluster121, PHP 8.2, Laravel 11 + Inertia

---

## État actuel

| URL | Status | Notes |
|-----|--------|-------|
| `praxiquest.decisionpro.fr/` | ✅ 200 | Home page OK |
| `praxiquest.decisionpro.fr/login` (GET) | ✅ 200 | Login form OK |
| `praxiquest.decisionpro.fr/login` (POST) | ❌ 500 | Crash à la soumission |
| `praxiquest.decisionpro.fr/onboarding` | ❌ 500 | Crash GET |

**Le 500 est déclenché par le POST /login** (soumission du formulaire). Le crash se produit probablement lors de l'écriture de session ou de la redirection post-login.

---

## Fixes déjà appliqués sur OVH

- [x] `QUEUE_CONNECTION` : `redis` → `sync`
- [x] `SESSION_DRIVER` : `redis` → `file`
- [x] `CACHE_STORE` + `CACHE_DRIVER` : `redis` → `file`
- [x] `DB_PASSWORD` : corrigé → `M5oi3Z27wep8tuoc`
- [x] `DB_CONNECTION` testé : `Users: 2` → ✅
- [x] Dossiers storage créés : `sessions/`, `cache/data/`, `views/`
- [x] Permissions `storage/` : `chmod -R 777`
- [x] `php artisan optimize:clear` : ✅
- [x] `APP_DEBUG=true` (temporaire pour debug)
- [x] `display_errors on` dans `.htaccess` (temporaire)

---

## Ce qui fonctionne en CLI

```bash
# DB OK
php -r "... echo App\Models\User::count();"
# → Users: 2 ✅

# Home page OK
php -r "... Request::create('/', 'GET') ..."
# → 200 ✅

# Plugins OK
$pm->bootEnabledPlugins();
# → OK ✅
```

---

## Hypothèse principale : Session write ou Redirect post-login

Le POST /login crashe. Pistes à investiguer :

### 1. Vérifier la session après login
```bash
# Après un POST /login, des fichiers doivent apparaître ici :
ls -la ~/www/PraxiTests/storage/framework/sessions/
```
Si le dossier est vide → la session ne s'écrit pas → **le crash est dans StartSession middleware**.

### 2. Vérifier le log immédiatement après POST /login
```bash
tail -30 ~/www/PraxiTests/storage/logs/laravel.log
```

### 3. Tester le POST /login en CLI avec session simulée
```bash
php -r "
require '/home/decisiv/www/PraxiTests/vendor/autoload.php';
\$app = require '/home/decisiv/www/PraxiTests/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Http\Kernel::class);
\$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email'    => 'alexandre.fradin@gmail.com',
    'password' => 'TON_MOT_DE_PASSE',
    '_token'   => 'test',
]);
\$response = \$kernel->handle(\$request);
echo \$response->getStatusCode() . PHP_EOL;
echo substr(\$response->getContent(), 0, 500) . PHP_EOL;
"
```

### 4. Vérifier les routes auth
```bash
cat ~/www/PraxiTests/routes/auth.php
```

### 5. Vérifier que email_verified_at n'est pas null (middleware `verified`)
```bash
php -r "
require '/home/decisiv/www/PraxiTests/vendor/autoload.php';
\$app = require '/home/decisiv/www/PraxiTests/bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Models\User::all(['id','email','email_verified_at'])->each(fn(\$u) => print(\$u->id.' | '.\$u->email.' | '.(\$u->email_verified_at ?? 'NULL').PHP_EOL));
"
```
Si `email_verified_at` est NULL → le middleware `verified` bloque → **ajouter `email_verified_at=now()` en DB**.

---

## Fichiers .htaccess à nettoyer après debug

Supprimer ces lignes ajoutées temporairement :
```
php_flag display_errors on
php_value error_reporting -1
php_value error_log /home/decisiv/www/PraxiTests/storage/logs/php_errors.log
```

---

## À faire après résolution du 500

- [ ] Remettre `APP_DEBUG=false` dans `.env`
- [ ] Nettoyer `.htaccess` (supprimer les lignes debug)
- [ ] Corriger `APP_URL=https://praxiquest.decisionpro.fr` (actuellement faux)
- [ ] Committer le fix `CvExtractionService.php` en local puis `git push` + `git pull` sur OVH
- [ ] Tester le flux complet : login → onboarding → CV → tests
