# Résolution 500 `/tests` + publication des tests — PraxiQuest

**Date :** 20 juin 2026
**Env :** OVH mutualisé Pro, cluster121, PHP 8.2, Laravel 11 + Inertia/Vue, app servie = `~/praxiquest`
**Point de départ :** `https://praxiquest.decisionpro.fr/tests` → **500 Server Error**
**Résultat :** site rétabli, **11 tests publiés et cliquables** ; reste 1 test (praxilink) à finaliser.

---

## Causes racines traitées (dans l'ordre)

### 1. `Profile::isComplete()` absent sur la prod → 500 sur `/tests`
`TestController` appelle `auth()->user()->profile?->isComplete()`. Le code local avait la méthode, **pas la prod** (code obsolète). Sans profil, `?->` court-circuite → OK ; dès qu'un profil existe (post-onboarding), la méthode est appelée → `BadMethodCallException` → 500 sur toutes les pages connectées.
**Fix :** `app/Models/Profile.php` réécrit proprement (méthode `isComplete()` = `status` + `status_since` + `cv_path` + `consent_data`) puis déployé.

### 2. Assets Vite manquants → page blanche
`/public/build` est **gitignoré**, et `deploy-server.sh` faisait `rm -rf public/build/` **avant** un `git pull` en *merge* : un merge ne restaure que les fichiers modifiés, donc les chunks inchangés (`_plugin-vue_export-helper-*.js`, `CandidateLayout-*.css`) restaient supprimés → MIME `text/html`/404 → l'app Vue plante.
**Fix :**
- `.gitignore` : `public/build` **suivi par git** (le mutualisé OVH n'a pas npm, le build doit être versionné).
- `deploy-server.sh` : suppression du `rm -rf public/build/`, ajout d'un `git checkout -- public/build` de sécurité après le pull.

### 3. Build Vite cassé — apostrophes doublées dans `Landing.vue`
`v-for` avec des chaînes JS en apostrophes doublées (`'L''ancrage'`, style SQL) → `SyntaxError` à `npm run build`.
**Fix :** échappement JS correct (`'L\'ancrage'`) sur les 4 lignes du `v-for`.

### 4. Un seul test en base (`orientation-express`)
`/tests` ne liste que les tests `published = true`. Les tests-plugins ne se créent qu'à **l'activation** : `praxiquest:plugins:activate <slug>` → `onActivate()` → seeder → `Test::updateOrCreate(... 'published'=>true)`.
**Fix :** `discover --sync` + activation des plugins.

### 5. 4 plugins non détectés par `discover`
`discover` ignore **silencieusement** les manifests qui échouent la validation. `praxilink`, `praxiself`, `praxispeak` sont `type:"mini-app"` et `praxis360` `type:"assessment"` — types absents de `config/plugins.php → available_types`.
**Fix :** ajout de `mini-app` et `assessment` à `available_types`.

### 6. `php artisan view:cache` plantait
Plusieurs plugins enregistrent un dossier `resources/views` inexistant (ils sont en Vue/Inertia) → le Finder de `view:cache` lève une exception → `set -e` casse le déploiement.
**Fix :** surcharge de `loadViewsFrom()` et `loadMigrationsFrom()` dans `AbstractPlugin` (garde `is_dir`). Stopgap serveur : `for d in plugins/*/; do mkdir -p "$d/resources/views"; done`.

### 7. Seeders praxis360 / praxiself — colonnes manquantes
- `test_questions.meta` n'existait pas (seeders praxiself/praxis360 l'écrivent).
- `test_norms.mean` / `std_dev` étaient NOT NULL alors que NormsSeeder insère `null` (« à calculer après 50 passations »).
**Fix :** migration `2026_06_20_000001_add_meta_to_questions_and_nullable_norms.php` (ajoute `meta` json nullable, rend `mean`/`std_dev` nullables) + cast `meta => array` sur `TestQuestion`.

### 8. praxilink — moteur de scoring non conforme (⏳ en cours)
`PraxiLinkScoringEngine` implémente une interface inexistante (`Praxis\Core\TestEngine\ScoringEngineInterface`) avec une signature `score(array $answers, array $context)` au lieu du contrat `ScoringEngineContract::score(TestAttempt): array`. Comme le `boot()` des plugins tourne **hors** du try/catch de `bootEnabledPlugins`, une fois `enabled=true` ça plante **tout artisan et le site**.
**Action prise :** désactivé en base sans artisan (PDO direct) :
```bash
php -r '$e=[];foreach(file(".env") as $l){if(preg_match("/^(DB_[A-Z_]+)=(.*)/",trim($l),$m))$e[$m[1]]=trim($m[2]," \"\x27");}$p=new PDO("mysql:host=".$e["DB_HOST"].";dbname=".$e["DB_DATABASE"],$e["DB_USERNAME"],$e["DB_PASSWORD"]);echo $p->exec("UPDATE plugins SET enabled=0 WHERE slug=\"praxilink\"");'
```
**Reste à faire :** porter le moteur sur le contrat standard (extraire les réponses via `$attempt->answers()->with('question')->get()`, comme `PraxiSelfScoringEngine`).

---

## Fichiers modifiés (repo local)

- `app/Models/Profile.php` — méthode `isComplete()`
- `app/Models/TestQuestion.php` — cast `meta => array`
- `app/Core/Plugins/AbstractPlugin.php` — garde `is_dir` sur `loadViewsFrom`/`loadMigrationsFrom`
- `config/plugins.php` — types `mini-app` + `assessment`
- `.gitignore` — `public/build` suivi par git
- `deploy-server.sh` — suppression `rm -rf public/build/` + `git checkout` de sécurité
- `resources/js/Pages/Public/Landing.vue` — apostrophes du `v-for`
- `database/migrations/2026_06_20_000001_add_meta_to_questions_and_nullable_norms.php` — nouvelle migration

---

## État des tests (11 publiés)

| Test | Plugin | Type | Statut |
|------|--------|------|--------|
| orientation-express | — (démo) | — | ✅ |
| praxicare | praxicare | test | ✅ |
| praxiemo | praxiemo | test | ✅ |
| praxiflow | praxiflow | test | ✅ |
| praximet | praximet | test | ✅ |
| praximum | praximum | test | ✅ |
| praxivaleurs | praxivaleurs | test | ✅ |
| praxizen | praxizen | test | ✅ |
| praxispeak | praxispeak | mini-app | ✅ |
| praxis360 | praxis360 | assessment | ✅ |
| praxiself | praxiself | mini-app | ✅ |
| praxilink | praxilink | mini-app | ⏳ désactivé — moteur à porter |

---

## Rappels déploiement (à retenir)

- **Sur le mutualisé OVH, ni `composer` ni `npm` dans le PATH** : composer = `php ~/composer.phar` ; le build Vite se fait **sur la machine Windows** (`deploy-ovh.ps1`) et est versionné.
- Toujours **2 temps** : (1) PC `deploy-ovh.ps1` (build + commit + push), (2) OVH `bash deploy-server.sh`. Sans l'étape 1, le serveur reste « Déjà à jour ».
- En cas de 500 : `grep "production.ERROR" ~/praxiquest/storage/logs/laravel.log | tail -1`.
- App servie = **`~/praxiquest`** uniquement.

---

## Prochaine étape

Porter `PraxiLinkScoringEngine` sur `ScoringEngineContract` (signature `score(TestAttempt): array`), puis :
```bash
php artisan praxiquest:plugins:activate praxilink   # → 12 tests
```
