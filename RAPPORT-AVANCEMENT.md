# PraxiQuest — Rapport d'avancement

> **Date :** 3 juin 2026 · **Version :** 1.0.0-alpha · **Dernier commit local :** `1df4c33`
> **Statut global :** cœur applicatif complet et reconstruit · **prêt à déployer**, pas encore poussé ni mis en ligne dans cette version.

---

## 1. Où en est le projet

PraxiQuest est un SaaS d'évaluation et d'orientation professionnelle (tests en ligne,
système de plugins, invitations par email, synthèse IA + 15 idées de métiers, gamification).
Le code applicatif est **fonctionnellement complet**. La période récente a surtout servi à
deux choses : (1) **durcir** le produit (audit sécurité/perf/qualité), et (2) **réparer une
corruption** de fichiers causée par la synchronisation du dossier de travail (voir §6).

À ce stade : le code est sain et committé en local, les correctifs majeurs sont appliqués,
mais il reste **trois jalons avant la mise en production** : pousser sur GitHub, valider à
l'exécution (build + tests), puis dérouler la mise en ligne OVH.

---

## 2. Ce qui est livré

| Domaine | Détail |
|---------|--------|
| Cœur Laravel 11 | Auth + reset password, 13 migrations, 15 modèles, rôles Spatie (admin/professional/candidate) |
| Système de plugins | Auto-discovery `plugin.json`, manager, hooks, commandes `praxiquest:plugins:*` |
| 5 tests | RIASEC (84 q.), Schwartz (40), Karasek+MBI (48), EQ-i (80+6), Big Five OCEAN (128) |
| IA | 4 drivers (Anthropic/OpenAI/Mistral/Ollama), synthèse profil + 15 métiers, extraction CV |
| Onboarding | Statut + ancienneté + CV **obligatoires avant** de passer un test |
| Gamification | XP, niveaux, badges, narration, insights |
| Mailing | Campagnes, séquences, invitations (lien public), optimiseur neuromarketing |
| Front | Inertia + Vue 3, 21 pages, design system Tailwind |
| Installeur web | `install.php` mono-formulaire, auto-verrouillé après install |
| Pipeline build | GitHub Actions : zip déployable (vendor + assets) à chaque push |

Volumétrie : ~14 contrôleurs, 15 modèles, 21 pages Vue, 54 tests automatisés.

---

## 3. Qualité & sécurité (synthèse)

Audit en 5 passes appliqué. **13 des 15 points de sécurité corrigés** (installeur non
contournable, rate-limiting, validation des plugins, whitelist des pages de résultats,
séparation des rôles, assainissement CV, etc.). Race conditions corrigées (XP, tentatives),
bugs psychométriques Big Five/EQ-i corrigés, dashboard optimisé, index de performance ajoutés.

**Point de sécurité encore ouvert :** SEC-07 — le HTML des emails de campagne n'est pas
assaini (`{!! $html !!}`). Atténué (réservé aux comptes admin/pro), à corriger avec HTMLPurifier.
Détails complets dans `AUDITS.md`.

---

## 4. Mise en ligne (détaillé)

### 4.1 Cible d'hébergement
- **Hébergeur :** OVH — domaine **decisionpro.fr**, **cluster121**, **plan Pro** (SSH + Composer + Node disponibles).
- **URL cible :** `https://praxiquest.decisionpro.fr`
- **Document root :** `praxiquest/public` (le `/public`, **pas** la racine du projet).
- **Base de données :** MySQL OVH — hôte de la forme `xxxxx.mysql.db` (**jamais** `localhost`), port 3306.

### 4.2 Pré-requis à régler une seule fois dans OVH Manager
1. **PHP 8.2** sur le domaine (Multisite → Modifier).
2. **Base MySQL** créée (noter nom, utilisateur, mot de passe, hôte).
3. **Sous-domaine** `praxiquest.decisionpro.fr` pointant vers `…/praxiquest/public`.
4. **SSH activé** (Multisite → onglet SSH) — host `sshXX.cluster121.hosting.ovh.net`.

### 4.3 Deux méthodes de déploiement

**Méthode A — SSH (recommandée, plan Pro).** Un script `deploy.sh` est fourni :
```
ssh ton-login@sshXX.cluster121.hosting.ovh.net
cd ~/www
bash deploy.sh first      # 1re installation : clone, .env, composer, migrate, seed, plugins, caches
bash deploy.sh update     # mises à jour suivantes : git pull, composer, migrate, caches
```
Le script enchaîne : `composer install --no-dev`, `php artisan key:generate`, `migrate --force`,
`db:seed`, découverte + activation des 5 plugins, `config/route/view:cache`, `storage:link`,
permissions `storage` et `bootstrap/cache`.

> ⚠ **Bug à corriger avant usage :** `deploy.sh` clone `…/PraxiQuest.git`, or le dépôt
> réel s'appelle **`PraxiTests`**. Corriger l'URL en `https://github.com/alexandrefradin-lab/PraxiTests.git`
> (ou renommer le dépôt GitHub) sinon `deploy.sh first` échoue au clone.

**Méthode B — Sans SSH (zip + FTP + installeur).** Pour un plan sans SSH ou en secours :
1. `git push` → **GitHub Actions** build automatiquement un zip complet (vendor + `public/build`)
   téléchargeable dans Actions → Artifacts (workflow `release.yml`).
2. Extraire le zip (sous Windows : 7-Zip vers un chemin court type `C:\pt\` pour éviter les
   chemins trop longs) puis **uploader via FileZilla** dans `…/www/praxiquest/`.
3. Ouvrir **`https://praxiquest.decisionpro.fr/install.php`**.

### 4.4 L'installeur web (`install.php`)
Formulaire unique qui : vérifie PHP/extensions/permissions/`vendor`, teste la connexion DB,
écrit le `.env` (clé APP_KEY générée, valeurs nettoyées contre l'injection), puis en AJAX
multi-étapes (pour contourner le timeout OVH de 30 s) : **wipe + migrate → seed → plugins +
storage:link → verrouillage**. Il se bloque ensuite via `storage/app/.installed`. Sécurité :
guard prioritaire (plus contournable par `?force`), endpoint AJAX protégé par le guard.

### 4.5 Activation de l'IA (après install)
Éditer le `.env` sur le serveur :
```
AI_DEFAULT_DRIVER=anthropic
ANTHROPIC_API_KEY=sk-ant-xxxxx
ANTHROPIC_MODEL=claude-sonnet-4-6
```
Sans clé, les tests fonctionnent mais la synthèse IA reste vide.
Recommandé sur OVH : `QUEUE_CONNECTION=database` + une tâche cron `php artisan queue:work`
pour que la génération IA (20–45 s) ne bloque pas la requête HTTP.

### 4.6 Compte admin par défaut
Défini via `.env` (`PRAXIQUEST_ADMIN_EMAIL` / `PRAXIQUEST_ADMIN_PASSWORD`). **À changer
immédiatement après la première connexion.**

### 4.7 État réel de la mise en ligne
- Pipeline de build et installeur : **prêts et fonctionnels.**
- **Non encore réalisé dans cette version :** le `git push` (pas d'identifiants côté assistant),
  donc GitHub Actions n'a pas rebuildé le dernier zip ; la mise en ligne effective n'a pas été
  relancée. Le statut « en production » du dernier code ne peut donc pas être confirmé.
- **Bloquant identifié :** l'URL de dépôt dans `deploy.sh` (§4.3) à corriger.

---

## 5. Validation restante (à faire en local / sur serveur)
Aucune exécution n'a pu être faite côté assistant (PHP/Node absents). À lancer :
```
composer install
php artisan key:generate
npm install && npm run build
.\vendor\bin\pest          # 54 tests
```
La cohérence du code a été vérifiée statiquement (0 fichier tronqué, structure équilibrée),
ce qui ne remplace pas un `php -l` ni l'exécution des tests.

---

## 6. Risques & points ouverts

| Sujet | Gravité | Action |
|-------|---------|--------|
| Dossier projet synchronisé (OneDrive) qui **tronque les écritures** — cause de la corruption | 🔴 | Déplacer le dépôt hors du dossier synchronisé (ex. `C:\dev\PraxiQuest`) |
| Travail non poussé sur GitHub | 🟠 | `git push` depuis un dossier sain |
| Validation runtime non faite | 🟠 | Build + migrate + tests en local |
| URL dépôt erronée dans `deploy.sh` | 🟠 | Remplacer `PraxiQuest.git` par `PraxiTests.git` |
| SEC-07 (XSS emails de campagne) | 🟠 | Assainir le HTML (HTMLPurifier) |
| Refonte visuelle des 5 pages de résultats perdue (irrécupérable) | 🟡 | À refaire si souhaitée |

---

## 7. Prochaines étapes recommandées (dans l'ordre)
1. Recréer le dépôt dans un dossier **non synchronisé** (à partir du bundle Git fourni).
2. `git push` → déclenche le build GitHub Actions.
3. Corriger l'URL de `deploy.sh`, puis valider en local (build + tests).
4. Déployer sur OVH (méthode SSH `deploy.sh first`), ouvrir `install.php`, configurer l'IA.
5. Corriger SEC-07 et changer le mot de passe admin.
