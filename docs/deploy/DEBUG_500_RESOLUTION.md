# Résolution des erreurs 500 — PraxiQuest (OVH)

**Date :** 19 juin 2026
**Env :** OVH mutualisé Pro, cluster121, PHP 8.2, Laravel 11 + Inertia/Vue
**URL :** https://praxiquest.decisionpro.fr

---

## ⚠️ Découverte majeure : 3 copies de l'app sur le serveur

Le serveur contient **trois copies** du projet. On a perdu beaucoup de temps à déboguer la mauvaise.

| Chemin | Rôle | Statut |
|--------|------|--------|
| **`~/praxiquest/`** | **App réellement servie** par `praxiquest.decisionpro.fr` (docroot = `~/praxiquest/public`) | ✅ C'est ICI qu'il faut travailler |
| `~/www/PraxiTests/` | Copie de travail (DB OK en CLI) — **non servie** | ⚠️ Ne pas confondre |
| `~/praxitests/` | Ancienne copie éparse | ❌ Obsolète |
| `~/www/` | Ancien site custom « DecisionPro » (PHP maison, pas Laravel) | Hors sujet |

**Preuve :** le vrai `laravel.log` est dans `~/praxiquest/storage/logs/` et la stack trace pointe vers `/home/decisiv/praxiquest/public/index.php`.

> 👉 **Règle pour la suite : tout déploiement / correctif doit cibler `~/praxiquest/`.**

---

## Causes racines trouvées et corrigées (dans l'ordre)

### 1. Cache / Session / Queue sur Redis (inexistant)
Le `.env` pointait vers Redis alors qu'il n'y a pas de serveur Redis sur cette offre.
**Fix :** `CACHE_STORE=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync` (déjà correct dans `~/praxiquest/.env`).

### 2. Middleware `verified` sans route de vérification
`routes/web.php` appliquait `['auth','verified']` mais aucune route `verification.notice` n'existe → 500 pour tout utilisateur non vérifié.
**Fix :** retrait de `verified` (gardé `auth`). Appliqué au repo local. *(À reporter sur `~/praxiquest` si le souci réapparaît pour un compte non vérifié.)*

### 3. 🎯 Mot de passe DB faux dans l'app servie — CAUSE PRINCIPALE du 500 login/onboarding
`~/praxiquest/.env` avait `DB_PASSWORD=PraxiQuest2024` → `SQLSTATE[HY000] [1045] Access denied`.
Toute requête SQL plantait (login, chargement user) → 500. Les pages invité sans DB passaient, d'où la confusion.
**Fix :** `DB_PASSWORD=M5oi3Z27wep8tuoc` dans `~/praxiquest/.env` → `Users: 2` ✅.

### 4. `status_months` négatif/flottant (onboarding)
`OnboardingController::store()` : `now()->diffInMonths($status_since)` renvoie une valeur **signée flottante** en Carbon 3 (ex. `-180.15`) → colonne `unsignedSmallInteger` refuse → 500.
**Fix :** `(int) abs(now()->diffInMonths($data['status_since']))`. Repo local + `~/praxiquest`.

### 5. Chaînage `when()` sur un entier (gamification)
`GamificationEngine::awardXp()` chaînait deux `->when()` ; le premier `increment()` renvoie un `int`, donc le second `->when()` plantait : *Call to a member function when() on int*.
**Fix :** réécriture en `if/else` simple. Repo local + `~/praxiquest`.

---

## Détail technique du diagnostic (pour mémoire)

- `APP_DEBUG=false` sur l'app servie → page « 500 SERVER ERROR » générique au lieu d'Ignition. Le **vrai** message était dans `~/praxiquest/storage/logs/laravel.log`.
- Méthode gagnante : `grep "production.ERROR" ~/praxiquest/storage/logs/laravel.log | tail -1` après chaque tentative.
- Penser à `php artisan optimize:clear` (dans `~/praxiquest`) après chaque modif.

---

## Fichiers modifiés

**Repo local** (`C:\Users\...\PraxiTests`) :
- `routes/web.php` — retrait `verified`
- `app/Http/Controllers/Candidate/OnboardingController.php` — `status_months`
- `app/Core/Gamification/GamificationEngine.php` — `awardXp` if/else

**Sur OVH `~/praxiquest/`** (patchés directement) :
- `.env` — `DB_PASSWORD`
- `OnboardingController.php` — `status_months`
- `GamificationEngine.php` — `awardXp`
- Sauvegardes : `.env.bak.*`, `/tmp/Onboarding.bak`, `/tmp/Gam.bak`

---

## À faire / nettoyage

- [ ] **Synchroniser le repo local → `~/praxiquest`** proprement (git) pour que les 3 fichiers corrigés en local soient la source de vérité.
- [ ] Supprimer les marqueurs de debug : `~/praxiquest/public/_which.txt`, `~/www/_which.txt`, `~/www/public/_which.txt`, `~/www/PraxiTests/public/_which.txt`.
- [ ] Nettoyer le `public/.htaccess` de `~/praxiquest` (vérifier qu'il a bien les règles de réécriture Laravel, pas seulement des lignes de debug).
- [ ] Supprimer les **fichiers parasites** dans `~/` créés par des copier-coller ratés (`APP_URL:`, `DB_HOST:`, `RewriteRule`, `Welcome`, `}`, `^C`, `EOF^C`, etc.).
- [ ] Décider du sort des copies obsolètes `~/www/PraxiTests` et `~/praxitests` (archiver/supprimer pour éviter toute future confusion).
- [ ] Vérifier le flux complet de bout en bout : login → onboarding → test → réponses → restitution (synthèse IA + 15 métiers).
- [ ] (Optionnel) Implémenter une vraie vérification d'email si on veut réactiver `verified`.
