# PraxiQuest — Documentation de reprise développeur

> **Version :** 1.0.0-alpha · **Commit de référence :** `bb7a4a1`  
> **Date :** Juin 2026 · **Propriétaire :** Alexandre Fradin (alexandre.fradin@gmail.com)  
> **Statut du projet :** MVP fonctionnel, non déployé en production

---

## Table des matières

1. [Vue d'ensemble du projet](#1-vue-densemble-du-projet)
2. [Stack technique](#2-stack-technique)
3. [Pré-requis environnement](#3-pré-requis-environnement)
4. [Installation locale (dev)](#4-installation-locale-dev)
5. [Structure du projet](#5-structure-du-projet)
6. [Architecture — plugin system](#6-architecture--plugin-system)
7. [Plugins disponibles](#7-plugins-disponibles)
8. [Couches transversales](#8-couches-transversales)
9. [Modèle de données](#9-modèle-de-données)
10. [Variables d'environnement clés](#10-variables-denvironnement-clés)
11. [Déploiement OVH](#11-déploiement-ovh)
12. [CI/CD GitHub Actions](#12-cicd-github-actions)
13. [Bugs connus & priorités](#13-bugs-connus--priorités)
14. [Roadmap MVP restante](#14-roadmap-mvp-restante)
15. [Conventions de code](#15-conventions-de-code)
16. [Contacts & ressources](#16-contacts--ressources)

---

## 1. Vue d'ensemble du projet

**PraxiQuest** est un SaaS propriétaire d'évaluation et d'orientation professionnelle. Il permet à des professionnels (coachs, conseillers bilan, RH) d'envoyer des invitations à passer des tests psychométriques, puis de recevoir une restitution automatisée par IA.

### Parcours utilisateur

1. **Invitation** — le professionnel envoie un lien par email
2. **Onboarding** — le candidat renseigne son statut (salarié / entrepreneur / demandeur d'emploi / étudiant), son ancienneté, et uploade son CV (obligatoire)
3. **Tests** — passation des tests disponibles (modules chargés via plugins)
4. **Restitution** — synthèse IA + 15 suggestions de métiers, gamification (badges, XP), export PDF
5. **Suivi** — dashboard professionnel, campagnes email automatisées, leads

### Différenciants techniques

- Architecture **plugin-first** : les tests sont des plugins indépendants, pas du code core
- **Abstraction IA** : driver swappable (Anthropic, OpenAI, Mistral, Ollama)
- **Gamification sobre** : engagement sans nuire à la crédibilité professionnelle
- **Neuromarketing intégré** : optimisation des emails et parcours (8 biais comportementaux)
- **Installeur web wizard** : déploiement sans CLI, style WordPress

---

## 2. Stack technique

| Couche | Technologie | Version |
|--------|-------------|---------|
| Backend | PHP + Laravel | 8.2+ / 11 |
| Frontend | Inertia.js + Vue 3 + Vite | — |
| CSS | Tailwind CSS | 3 |
| Base de données | MySQL ou PostgreSQL | 8 / 15 |
| Cache / Queue | Redis (optionnel) ou file/database | — |
| Auth | Laravel Sanctum + sessions | 4 |
| Permissions | spatie/laravel-permission | 6 |
| Logs d'activité | spatie/laravel-activitylog | 4.8 |
| PDF (export) | barryvdh/laravel-dompdf | 3 |
| Parsing CV | smalot/pdfparser | 2.7 |
| Paiement | Laravel Cashier (Stripe) | 15 |
| Tests PHP | Pest | 3 |
| Linting PHP | Laravel Pint | — |
| Tests JS | Vitest | — |

---

## 3. Pré-requis environnement

### Local (développement)

- PHP **8.2+** avec extensions : `pdo`, `mbstring`, `openssl`, `fileinfo`, `json`, `tokenizer`, `xml`, `ctype`, `curl`, `bcmath`
- Composer 2+
- Node.js 22+ et npm
- MySQL 8 ou PostgreSQL 15
- Redis (optionnel mais recommandé pour la queue IA)

### OVH (production)

- Plan **Pro** (pas Perso) — SSH requis pour `composer install` et `npm run build`
- PHP 8.2 activé dans le Manager OVH
- DB host : `XXXX.mysql.db` (jamais `localhost` sur OVH mutualisé)
- Document root configuré sur `praxiquest/public` (jamais la racine)

> ⚠️ **Alerte OneDrive :** le dossier de développement est dans `Documents\`, potentiellement synchronisé par OneDrive. OneDrive tronque les écritures de fichiers et corrompt l'index Git. **Déplacer le dépôt vers `C:\dev\PraxiQuest`** ou suspendre la synchronisation OneDrive sur ce dossier avant tout travail.

---

## 4. Installation locale (dev)

```bash
# 1. Cloner le dépôt
git clone <repo> praxiquest
cd praxiquest

# 2. Dépendances PHP
composer install

# 3. Dépendances JS
npm install

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer .env (DB, mail, clés IA)
# Voir section 10 pour les variables clés

# 6. Migrations + seeders
php artisan migrate --seed

# 7. Lien storage
php artisan storage:link

# 8. Découvrir et activer les plugins
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate praximet
php artisan praxiquest:plugins:activate praxivaleurs
php artisan praxiquest:plugins:activate praxicare
php artisan praxiquest:plugins:activate praxiemo
php artisan praxiquest:plugins:activate praximum

# Ou tout en une commande :
composer run praxiquest:install

# 9. Build frontend
npm run dev       # développement (hot reload)
npm run build     # production
```

### Lancer les tests

```bash
./vendor/bin/pest        # tous les tests
./vendor/bin/pest --coverage
```

### Vérification post-install

```bash
php artisan route:list        # vérifier que toutes les routes sont chargées
php artisan praxiquest:plugins:list  # vérifier les plugins actifs
```

---

## 5. Structure du projet

```
praxiquest/
├── app/
│   ├── Console/Commands/         # CLI artisan (plugin:install, discover, etc.)
│   ├── Core/                     # Cœur métier (namespace Praxis\Core\*)
│   │   ├── AI/                   # Drivers IA + PromptBuilder + services
│   │   ├── Gamification/         # GamificationEngine, BadgeEvaluator, NarrativeEngine
│   │   ├── Mailing/              # CampaignService, SequenceRunner, NeuromarketingOptimizer
│   │   ├── Plugins/              # PluginManager, PluginHooks, PluginRegistry
│   │   └── TestEngine/           # TestEngine, NormInterpreter, ScoringEngineContract
│   ├── Http/Controllers/
│   │   ├── Admin/                # Dashboard, Leads, Tests, Plugins, Settings
│   │   ├── Auth/                 # AuthController (register, login, logout, reset)
│   │   └── Candidate/            # OnboardingController, AttemptController, ResultController
│   ├── Jobs/
│   │   ├── GenerateAttemptInsights.php   # Job IA principal (synthèse + 15 métiers)
│   │   ├── ExtractCvDataJob.php          # Extraction structurée du CV
│   │   └── SendSequenceStepJob.php       # Envoi d'un step de séquence email
│   ├── Models/                   # Eloquent models (voir section 9)
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── PraxiQuestServiceProvider.php # Binding core + macros
├── config/
│   ├── praxiquest.php            # Config principale (statuts, CV, branding, features)
│   ├── ai.php                    # Drivers IA + tâches
│   ├── plugins.php               # Config plugin system
│   ├── gamification.php          # Niveaux, XP, badges
│   ├── neuromarketing.php        # Biais activés
│   └── plans.php                 # Plans tarifaires
├── database/
│   ├── migrations/               # 13 fichiers (voir section 9)
│   └── seeders/                  # DatabaseSeeder + seeders par domaine
├── plugins/                      # Plugins auto-discovered
│   ├── _template/                # Template de départ pour créer un plugin
│   ├── praximet/                 # Test RIASEC Holland (84 questions)
│   ├── praxivaleurs/             # Test valeurs Schwartz
│   ├── praxicare/                # Test Karasek + MBI (stress/burnout)
│   ├── praxiemo/                 # Test EQ-i (intelligence émotionnelle)
│   ├── praximum/                 # Test Big Five (personnalité)
│   ├── praxiflow/                # (en développement)
│   ├── praxilink/                # (en développement)
│   ├── praxiself/                # (en développement)
│   ├── praxispeak/               # (en développement)
│   └── praxizen/                 # (en développement)
├── docs/
│   └── PLUGIN-DEVELOPER.md       # Guide complet création plugin
├── public/
│   ├── install.php               # Installeur wizard standalone
│   └── index.php
├── resources/
│   ├── js/
│   │   ├── Pages/                # Pages Inertia (Admin/, Auth/, Candidate/)
│   │   ├── Components/           # Composants Vue réutilisables
│   │   ├── Layouts/              # AppLayout, AdminLayout, GuestLayout
│   │   └── Composables/          # useGameProgress, useNeuromarketing, etc.
│   └── views/                    # Blade (emails, installeur, PDF)
├── routes/
│   ├── web.php                   # Routes candidat + invitation publique
│   ├── admin.php                 # Routes back-office (rôles admin/professional)
│   ├── auth.php                  # Routes authentification
│   └── console.php               # Commandes scheduled
├── .github/workflows/
│   └── release.yml               # CI/CD : build + zip + GitHub Release
└── .env.example                  # Template variables d'environnement
```

---

## 6. Architecture — plugin system

### Principe

Le core est minimal. Chaque test est un plugin indépendant dans `/plugins/{slug}/`. Les plugins sont auto-découverts au boot via `PluginManager::discover()` qui scanne tous les `plugin.json`.

### Contrat `plugin.json`

```json
{
  "slug": "praximet",
  "name": "PraxiMet — Test RIASEC",
  "version": "3.0.0",
  "author": "Praxis Accompagnement",
  "type": "test",
  "namespace": "Praxis\\Plugins\\PraxiMet",
  "service_provider": "Praxis\\Plugins\\PraxiMet\\PluginServiceProvider",
  "permissions": ["read:profiles", "write:results", "send:mail"],
  "requires": { "praxiquest": ">=1.0.0", "php": ">=8.2" }
}
```

**Types de plugins valides :** `test`, `scoring`, `ai`, `mail`, `gamification`, `integration`, `theme`, `reporting`

### Service Provider (étend `AbstractPlugin`)

```php
class PluginServiceProvider extends AbstractPlugin
{
    public function boot(): void
    {
        $this->loadRoutesFrom($this->pluginPath('routes/plugin.php'));
        $this->loadViewsFrom($this->pluginPath('resources/views'), $this->slug());
        $this->loadMigrationsFrom($this->pluginPath('database/migrations'));

        $this->registerActions([
            'attempt.completed' => [$this, 'onAttemptCompleted'],
        ]);
        $this->registerFilters([
            'jobs.suggested' => [$this, 'enrichJobs'],
        ]);
    }
}
```

### Hooks disponibles

| Hook | Type | Args |
|------|------|------|
| `attempt.completed` | Action | `TestAttempt` |
| `attempt.started` | Action | `TestAttempt` |
| `attempt.answered` | Action | `TestAttempt, questionId, value` |
| `profile.completed` | Action | `Profile` |
| `ai.synthesis.completed` | Action | `TestAttempt, text` |
| `jobs.suggested` | Filter | `array $jobs, TestAttempt` |
| `results.inertia_page` | Filter | `string $page, TestAttempt` |
| `email.subject` | Filter | `string $subject, array $context` |

### Commandes artisan plugins

```bash
php artisan praxiquest:plugins:list                   # lister tous les plugins
php artisan praxiquest:plugins:discover --sync        # synchroniser BDD
php artisan praxiquest:plugins:activate {slug}        # activer
php artisan praxiquest:plugins:deactivate {slug}      # désactiver
```

### Autoload dans composer.json

Chaque nouveau plugin doit être ajouté manuellement dans `composer.json` sous `autoload.psr-4` ET `autoload.classmap` (pour le PluginServiceProvider), puis `composer dump-autoload`.

---

## 7. Plugins disponibles

| Slug | Nom | Type | Questions | Statut |
|------|-----|------|-----------|--------|
| `praximet` | PraxiMet — RIASEC | Test Holland | 84 binaires | ✅ Stable |
| `praxivaleurs` | PraxiValeurs — Schwartz | Valeurs | ~60 | ✅ Stable |
| `praxicare` | PraxiCare — Karasek/MBI | Stress/Burnout | ~50 | ✅ Stable |
| `praxiemo` | PraxiEmo — EQ-i | Intelligence émotionnelle | 86 | ✅ Stable |
| `praximum` | PraxiMum — Big Five | Personnalité | 60 | ✅ Stable (voir bug QC-19/QC-24) |
| `praxiflow` | PraxiFlow | — | — | 🔧 En cours |
| `praxilink` | PraxiLink | — | — | 🔧 En cours |
| `praxiself` | PraxiSelf | — | — | 🔧 En cours |
| `praxispeak` | PraxiSpeak | — | — | 🔧 En cours |
| `praxizen` | PraxiZen | — | — | 🔧 En cours |

### Créer un nouveau plugin

Partir du template `plugins/_template/` — voir `docs/PLUGIN-DEVELOPER.md` pour le guide complet.

---

## 8. Couches transversales

### 8.1 Couche IA

**Driver par défaut :** Anthropic Claude (configurable via `AI_DEFAULT_DRIVER`)

```php
// Utilisation
AI::driver('anthropic')->generate($prompt);
AI::driver()->chat($messages);  // driver par défaut
```

**3 services principaux** (`app/Core/AI/Services/`) :
- `ProfileSynthesisService` — synthèse narrative du profil
- `JobSuggestionService` — génération des 15 suggestions de métiers
- `CvExtractionService` — extraction structurée des données CV

**Configuration** (`config/ai.php`) : driver par tâche, modèle, température, max_tokens.

**Job IA** : `GenerateAttemptInsights` — s'exécute après la complétion d'un test. Chaîne : extraction CV → synthèse profil → suggestions métiers → sauvegarde `TestResult`.

> ⚠️ Sur OVH avec `QUEUE_CONNECTION=sync`, ce job s'exécute pendant la requête HTTP (20–45 s). Passer à `database` + cron est fortement recommandé (voir bug P-07).

### 8.2 Gamification

`GamificationEngine` (`app/Core/Gamification/`) :
- `awardXp(User $user, int $amount, string $reason)` — incrément atomique SQL
- `checkBadges(User $user, string $event)` — évaluation `BadgeEvaluator`
- `getProgress(User $user, int $testId)` — retourne XP, niveau, badges
- `NarrativeEngine` — génère des messages narratifs contextuels pendant la passation

Configuration : `config/gamification.php` (paliers XP, seuils de niveau).

### 8.3 Emailing / Neuromarketing

`CampaignService` et `SequenceRunner` (`app/Core/Mailing/Services/`) :
- Triggers automatiques sur events : `attempt.completed`, `lead.captured`, `inactive_7days`
- Templates Blade + variables dynamiques (profil, résultats, suggestions de métiers)
- `NeuromarketingOptimizer` : 8 biais comportementaux (urgence, rareté, preuve sociale, ancrage, Zeigarnik, anticipation, réciprocité, gratification immédiate)
- A/B test sur subject lines et CTAs

**Séquences email types :**
- Post-test : J+0 (résultats), J+3 (approfondissement), J+7 (action)
- Nurture lead : 5 étapes
- Re-engagement : inactif 7 jours
- Invitation à passer le test
- Rappel test non terminé

### 8.4 Installeur web

`public/install.php` — wizard 7 étapes standalone (tourne avant Laravel) :
1. Check requirements PHP
2. Configuration base de données
3. Création compte admin
4. Clé de licence
5. Branding (nom, logo)
6. Configuration SMTP
7. Migration + seed + activation plugins

Bloqué après installation par `storage/app/.installed`.

> ⚠️ **Bugs critiques installeur** SEC-01 et SEC-02 (voir section 13) : corriger avant tout déploiement.

---

## 9. Modèle de données

### Migrations (ordre d'exécution)

| Fichier | Tables créées |
|---------|--------------|
| `000001_create_users_table` | `users` |
| `000002_create_profiles_table` | `profiles` |
| `000003_create_plugins_table` | `plugins` |
| `000004_create_tests_table` | `tests`, `test_sections`, `test_questions`, `test_answers` |
| `000005_create_test_attempts_table` | `test_attempts`, `test_results` |
| `000006_create_gamification_tables` | `gamification_progress`, `badges`, `user_badges`, `journal_progress` |
| `000007_create_email_tables` | `email_campaigns`, `email_sequences`, `email_logs` |
| `000008_create_leads_and_pro_tables` | `leads`, `professional_accounts` |
| `000009_create_jobs_and_notifications_tables` | `jobs`, `failed_jobs`, `notifications` |
| `000010_create_permission_tables` | Tables spatie/permission |
| `000011_create_settings_table` | `settings` |
| `000012_create_test_norms_table` | `test_norms` |
| `000001_add_performance_indexes` (2026-06) | Index de performance |

### Entités principales

```
users           → id, email, password, role, locale
profiles        → user_id, status (employee/entrepreneur/jobseeker/student/other),
                  status_since, cv_path, cv_extracted_text, metadata (json)
test_attempts   → id, user_id, test_id, invitation_id, status, current_section,
                  started_at, completed_at, time_spent
test_results    → attempt_id, scoring (json), ai_synthesis (text),
                  suggested_jobs (json), generated_at
test_invitations → id, professional_id, email, token, status, sent_at, started_at, completed_at
```

### Rôles (spatie/permission)

- `admin` — accès complet
- `professional` — dashboard pro, ses tests, ses leads, ses campagnes
- `candidate` — passation + restitution de ses propres tests

---

## 10. Variables d'environnement clés

```dotenv
# Application
APP_NAME=PraxiQuest
APP_ENV=production          # local | production
APP_KEY=                    # généré par php artisan key:generate
APP_URL=https://votredomaine.fr
APP_LOCALE=fr

# Base de données
DB_CONNECTION=mysql
DB_HOST=XXXX.mysql.db       # OVH : jamais localhost
DB_PORT=3306
DB_DATABASE=praxiquest
DB_USERNAME=praxiquest_user
DB_PASSWORD=

# Queue (CRITIQUE pour le job IA)
QUEUE_CONNECTION=database   # sync (dev) | database (OVH) | redis (dédié)
CACHE_STORE=file            # file | redis
SESSION_DRIVER=file         # file | redis | database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com   # Brevo recommandé en prod
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votredomaine.fr

# IA
AI_DEFAULT_DRIVER=anthropic  # anthropic | openai | mistral | ollama
ANTHROPIC_API_KEY=
OPENAI_API_KEY=
MISTRAL_API_KEY=
ANTHROPIC_MODEL=claude-sonnet-4-6

# Features (optionnel)
PRAXIQUEST_GAMIFICATION_ENABLED=true
PRAXIQUEST_NEUROMARKETING_ENABLED=true
PRAXIQUEST_MULTITENANT=false        # non implémenté en v1
```

---

## 11. Déploiement OVH

### Prérequis OVH

- Plan **Pro** (SSH disponible)
- Domaine : `decisionpro.fr` (cluster121, IP 188.165.53.185)
- Sous-domaine recommandé : `praxitests.decisionpro.fr` ou `app.decisionpro.fr`
- Document root configuré sur `praxiquest/public`

### Procédure de déploiement

**Option A — Via l'installeur web (premier déploiement)**

1. Télécharger le zip depuis GitHub Releases (produit par le CI)
2. Uploader et extraire sur le serveur dans `praxiquest/`
3. Configurer le document root OVH sur `praxiquest/public`
4. Ouvrir `https://votredomaine.fr/install.php` et suivre le wizard

**Option B — Via SSH (mise à jour)**

```bash
ssh user@cluster121.hosting.ovh.net

cd ~/praxiquest

# Mettre en maintenance
php artisan down

# Récupérer les sources
git pull origin main
# ou dézipper le nouveau release

# Dépendances
composer install --no-dev --optimize-autoloader

# Build (si node disponible, sinon uploader le dossier public/build local)
npm ci && npm run build

# Migrations
php artisan migrate --force

# Caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Plugins
php artisan praxiquest:plugins:discover --sync

# Sortir de maintenance
php artisan up
```

### Queue worker sur OVH (cron)

Dans le Manager OVH, ajouter un cron :
```
*/5 * * * *   php /home/user/praxiquest/artisan queue:work --max-time=270 --tries=3
```

Ou avec `schedule:run` si vous préférez les tâches planifiées Laravel :
```
* * * * *   php /home/user/praxiquest/artisan schedule:run
```

### .htaccess

OVH utilise Apache. Le fichier `public/.htaccess` Laravel standard fonctionne tel quel. S'assurer que `mod_rewrite` est activé (c'est le cas par défaut sur OVH Pro).

---

## 12. CI/CD GitHub Actions

Fichier : `.github/workflows/release.yml`

**Déclencheurs :**
- Push sur `main`/`master`
- Push d'un tag `v*`
- Déclenchement manuel (workflow_dispatch)

**Ce que fait le pipeline :**
1. Setup PHP 8.2 + Composer
2. Setup Node 22 + npm
3. `composer install --no-dev --optimize-autoloader`
4. `npm install && npm run build`
5. Assemble un dossier `dist/` (sans `.env`, sans `node_modules`)
6. Crée `praxiquest-{version}.zip`
7. Uploade en artifact GitHub Actions (30 jours de rétention)
8. Si tag `v*` → crée une GitHub Release avec le zip en téléchargement

**Convention de version :** tags sémantiques `v1.0.0`, `v1.1.0`, etc.

---

## 13. Bugs connus & priorités

> Source : audit de sécurité et performance Juin 2026 (commit `bb7a4a1`).

### 🔴 Critiques — corriger avant mise en production

| ID | Fichier | Description | Fix rapide |
|----|---------|-------------|-----------|
| **SEC-01** | `public/install.php` | Guard post-install bypassable avec `?force=1` | Remplacer par `if (file_exists($flag)) { http_response_code(403); exit; }` sans exception |
| **SEC-02** | `public/install.php` | Bloc AJAX exécuté AVANT le guard | Déplacer le guard en toute première ligne du fichier |
| **QC-13** | `GamificationEngine.php:22` | Race condition XP — double incrément possible | `GamificationProgress::where(...)->increment('xp_total', $amount)` |
| **QC-14** | `TestEngine.php:30` | Double création de `TestAttempt` sur double-clic | Entourer de `DB::transaction()` avec `lockForUpdate()` |
| **QC-24** | `BigFiveScoringEngine.php:128` | Calcul percentile T-score incorrect (approximation linéaire au lieu de CDF normale) | Supprimer `tToPct()`, utiliser `NormInterpreter::fromTScore($T)['percentile']` |
| **QC-19** | `BigFiveScoringEngine.php:57` | Clé `'sd'` incohérente avec `'std_dev'` de NormInterpreter | Uniformiser en `'std_dev'` dans `Catalog::normes()` |

### 🟠 Élevés — corriger au plus tôt

| ID | Fichier | Description |
|----|---------|-------------|
| **P-07** | `AttemptController.php:88` | Job IA synchrone bloquant (20–45 s) → timeout OVH. Passer `QUEUE_CONNECTION=database` + cron |
| **SEC-03** | `routes/auth.php` | Aucun rate limiting sur login/register/forgot-password → brute-force. Ajouter `throttle:5,1` |
| **SEC-05** | `PluginManager.php:48` | `service_provider` non validé → RCE potentiel si FTP compromis. Valider le namespace dans `PluginManifestValidator` |
| **SEC-07** | `mail/layouts/campaign.blade.php:22` | XSS stocké via `{!! $html !!}` sans sanitisation. Utiliser HTMLPurifier |
| **FE-01** | `AttemptPlay.vue` | Crash page blanche si le test n'a aucune question |
| **FE-02** | `AttemptPlay.vue` | Double soumission possible sur double-clic "Suivant" |
| **P-08** | `CampaignService.php:74` | `User::get()` charge toute la table → saturation mémoire. Utiliser `.cursor()` |

### 🟡 Moyens — à planifier

| ID | Description |
|----|-------------|
| **SEC-09** | `markEmailAsVerified()` inconditionnelle — conditionner à l'env local/test |
| **SEC-10** | Injection `.env` par newline dans l'installeur — sanitiser les valeurs |
| **SEC-12** | `cv_original_name` sans sanitisation — utiliser `basename()` |
| **QC-20** | `EqiScoringEngine::desirabilite()` — logique de biais social inversée (vérifier direction items DS) |
| **QC-01** | `AnthropicDriver::chat()` retourne vide silencieusement — lever une exception |

---

## 14. Roadmap MVP restante

| Phase | Contenu | Statut |
|-------|---------|--------|
| Phase 0 — Base | Scaffolding, DB, auth, profil, installeur | ✅ Complet |
| Phase 1 — Tests | Moteur tests, plugin system, 5 tests | ✅ Complet |
| Phase 2 — IA | Driver IA, synthèse + 15 métiers | ✅ Complet |
| Phase 3 — Gamification | Badges, XP, progression, narration | ✅ Complet |
| Phase 4 — Email | Campagnes, séquences, neuromarketing | ✅ Complet (non testé en prod) |
| Phase 5 — Admin | Dashboard pro, scoring leads, stats | ✅ Complet |
| Phase 6 — Polish | Multi-tenant, white-label, RGPD, docs | 🔧 Partiel |

### Tâches restantes prioritaires

1. **Corriger les bugs critiques** (section 13 — surtout SEC-01/02 avant tout déploiement)
2. **Tester le parcours complet** en local (install → onboarding → test → restitution IA)
3. **Configurer la queue** `database` + cron OVH pour les jobs IA
4. **Configurer Brevo** (ou autre SMTP) pour les campagnes email
5. **Refonte visuelle des pages résultats** plugins (graphiques radar Chart.js, percentiles) — était en cours, irrécupérable dans le dernier commit stable
6. **Édition profil post-onboarding** — fonctionnalité retirée, à recréer si souhaitée
7. **Tests Pest manquants** : coverage sur `GamificationEngine`, `PluginManager`, `CampaignService`
8. **Multi-tenant** : `PRAXIQUEST_MULTITENANT=false` — non implémenté, à activer manuellement

---

## 15. Conventions de code

### PHP

- Style : **PSR-12** + `Laravel Pint` (`composer run lint`)
- Namespace core : `Praxis\Core\*`
- Namespace plugins : `Praxis\Plugins\{PluginName}\*`
- Services dans `app/Services/` ou `app/Core/*/Services/`
- Pas de logique métier dans les controllers (extraire en services)

### JavaScript / Vue

- **ESLint + Prettier**
- Pages Inertia dans `resources/js/Pages/`
- Composants réutilisables dans `resources/js/Components/`
- Composables dans `resources/js/Composables/`

### Git

- **Conventional Commits** : `feat:`, `fix:`, `docs:`, `refactor:`, `test:`
- Branches : `main` (stable), `develop`, `feature/{slug}`, `release/{version}`
- Tags sémantiques : `v1.0.0`

### Tests

```bash
# Lancer tous les tests
./vendor/bin/pest

# Avec coverage
./vendor/bin/pest --coverage --min=80
```

---

## 16. Contacts & ressources

| Ressource | Détail |
|-----------|--------|
| **Propriétaire** | Alexandre Fradin — alexandre.fradin@gmail.com |
| **Hébergement** | OVH Pro — cluster121 — decisionpro.fr |
| **Domaine cible** | praxitests.decisionpro.fr (recommandé) |
| **Documentation plugin** | `docs/PLUGIN-DEVELOPER.md` |
| **Architecture** | `ARCHITECTURE.md` (racine du projet) |
| **Audit sécurité/perf** | `AUDIT.md` et `AUDITS.md` (racine du projet) |
| **Template plugin** | `plugins/_template/` |
| **CI/CD** | `.github/workflows/release.yml` |
| **Config principale** | `config/praxiquest.php` |

---

*Document généré le 18 juin 2026 — à mettre à jour à chaque release majeure.*
