# HANDOFF — PraxiTests

> Document de reprise pour continuer le projet dans une nouvelle conversation Claude. Mets-le en pièce jointe ou colle son contenu en début de chat.

---

## 1. Mission

**PraxiTests** = SaaS propriétaire d'évaluation et orientation professionnelle augmenté par 3 leviers :

1. **IA** — synthèse profil + 15 métiers (drivers Anthropic / OpenAI / Mistral / Ollama)
2. **Neuromarketing** — optimisation conversion (8 biais cognitifs : scarcity, urgency, social proof, Zeigarnik, etc.)
3. **Gamification** — XP, niveaux, badges, narration, insights débloquables

**Architecture plugin-first** : tout ce qui peut bouger est en plugin (tests, scoring, IA, mail, gamification, intégrations, thèmes).

**Cible** : multi-flexible (B2C candidat / B2B RH / B2B2C écoles & orga).

---

## 2. État technique actuel (mai 2026)

### Stack

| Couche | Tech |
|--------|------|
| Backend | PHP 8.2 + Laravel 11 |
| Frontend | Inertia.js + Vue 3 + Vite |
| Styling | Tailwind CSS + design system custom (`pt-card`, `pt-btn-primary`, gradient indigo→emerald) |
| DB | MySQL 8 / PostgreSQL 15 |
| Cache/Queue | Redis |
| IA | driver abstraction multi-LLM |

### Workspace

`C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests`

### Compteurs

- **194 fichiers livrables** (hors vendor/, node_modules/, _imports/)
- **95 fichiers PHP**
- **19 pages Vue**
- **10 migrations DB**
- **5 seeders**
- **6 configs**
- **5 plugins WP convertis** (41 fichiers plugins)
- **6 tests Pest scoring engines**

---

## 3. Hébergement cible

**OVH Pro — décisionpro.fr — cluster121** — PHP 8.2 actif, CDN Basic, IP 188.165.53.185.

→ **SSH disponible** (composer + npm utilisables sur le serveur).

URL prévue : `https://praxitests.decisionpro.fr` (sous-domaine à créer dans OVH Manager).

---

## 4. Ce qui est livré

### Core Laravel

- Auth Sanctum + sessions web
- Migrations : users, profiles, plugins, tests, sections, questions, attempts, answers, results, invitations, gamification, emails, leads, pro_accounts, audit_logs, jobs, permissions
- Modèles Eloquent : User, Profile, Test, TestSection, TestQuestion, TestAttempt, TestAnswer, TestResult, TestInvitation, Plugin, Badge, GamificationProgress, Lead, ProfessionalAccount
- Controllers Candidate (Onboarding, Test, Attempt, Result) + Admin (Dashboard, Tests, Plugins, Leads, Campaigns) + Auth (Login/Register)
- Roles Spatie : admin / professional / candidate (13 permissions)
- Seeder admin par défaut configurable via `.env`

### Plugin system

- `Praxis\Core\Plugins\PluginContract` + `AbstractPlugin`
- `PluginManager` (boot, activate, deactivate, uninstall, sandbox)
- `PluginRegistry` (auto-discovery `/plugins/*/plugin.json`)
- `PluginHooks` (actions + filters style WordPress)
- `PluginManifestValidator` (sécurité, permissions explicites)
- 2 commandes Artisan : `praxitests:plugins:discover --sync`, `praxitests:plugins:activate {slug}`

### IA driver abstraction

- `AIManager` + `PromptBuilder`
- 4 drivers : `AnthropicDriver`, `OpenAiDriver`, `MistralDriver`, `OllamaDriver`
- 3 services : `ProfileSynthesisService`, `JobSuggestionService` (15 métiers), `CvExtractionService`
- Job `GenerateAttemptInsights` (queue async)

### Moteur de tests

- `TestEngine` (start, recordAnswer, complete) + `DefaultScoringEngine`
- Scoring engines extensibles via plugins
- Hooks `attempt.started`, `attempt.answered`, `attempt.completed`, `attempt.scoring`

### Gamification

- `GamificationEngine` (awardXp, levelFromXp, progressOf, unlockInsight)
- `BadgeEvaluator` (auto-attribution badges)
- `NarrativeEngine` (intro, midway, final, microFeedback)
- 5 badges seedés : first_step, completionist, analyzer, speedrunner, introspective

### Neuromarketing + Emailing

- `NeuromarketingOptimizer` (8 biais : scarcity, urgency, social_proof, authority, reciprocity, commitment, anchoring, zeigarnik)
- `CampaignService` (envoi groupé + A/B test variants)
- `SequenceRunner` (drip emails sur trigger event)
- Templates Blade `mail.layouts.campaign`

### Frontend Vue

| Layout | Pages |
|--------|-------|
| `CandidateLayout` | Onboarding (statut/CV/consent), TestsIndex, AttemptPlay (gamifié), ResultsShow |
| `AdminLayout` | Dashboard, Tests/Index, Tests/Edit, Plugins/Index, Plugins/Show, Campaigns/Index, Campaigns/Edit, Leads/Index, Leads/Show |
| `AuthLayout` | Login, Register |
| (root) | Public/Landing |

Design system : `pt-card`, `pt-btn-primary`, `pt-btn-ghost`, `pt-input`, `pt-badge`, `pt-progress-track`, `pt-progress-fill`, gradient signature `from-indigo-500 to-emerald-500`, police Inter.

### 5 plugins WP convertis

| Slug | Nom | Test | Questions | Scoring engine |
|------|-----|------|-----------|----------------|
| `praximet` | PraxiMet | RIASEC + leads | 84 binaires (6 types × 2 sous-domaines × 7) | code 3 lettres Holland |
| `praxivaleurs` | PraxiValeurs | Valeurs Schwartz | 40 Likert 1-6 | top 5 valeurs (10 dims) |
| `praxicare` | PraxiCare | Karasek + MBI | 48 (D/L/S/EE/DP/AP) | 5 profils (détendu, actif, passif, tendu, iso_strain) + sévérité MBI |
| `praxiemo` | PraxiEmo | Intelligence émotionnelle | 80 + 6 désirabilité | EQ-i 16 dim · 4 familles · niveau global QE |
| `praximum` | PraxiMum | Big Five OCEAN | 128 (120 facettes + 8 DS) | scoring T normé · 30 facettes · 16 archétypes (HHHHL etc.) |

Chaque plugin :
- `plugin.json` manifest
- `PluginServiceProvider.php`
- `src/Data/` (questions JSON ou PHP)
- `src/Scoring/` (ScoringEngine)
- `database/seeders/` (seeder questions)
- `resources/js/Pages/{Plugin}Result.vue` (page restitution dédiée, design system PraxiTests)
- `README.md`

PraxiMum a en plus `src/Archetypes/ArchetypeResolver.php` (16 archétypes, fallback Hamming).

PraxiMet a en plus `src/Listeners/OnAttemptCompleted.php` (génération auto de leads qualifiés).

### Installeur web

`public/install.php` — 431 lignes, autonome :
- Wizard 7 étapes : welcome → requirements → database → admin → branding → mail → license → install → success
- **Auto-création DB** : si la base n'existe pas et que le user a les droits, l'installeur la crée (`CREATE DATABASE IF NOT EXISTS`). Sinon hint clair pour création manuelle (cas OVH).
- Boot Laravel programmatiquement (require autoload + Kernel + commands)
- Exécute migrations, seeders, plugin activation automatiquement
- Lock après install via `storage/app/.installed`
- Logs détaillés visibles si erreur

### Distribution

- `make-release.bat` / `make-release.sh` → produit zip pré-buildé (vendor/ + public/build/ inclus)
- `setup.bat` / `setup.sh` → install local dev
- `dev.bat` / `dev.sh` → 3 processus (Laravel serve + Vite + queue worker)
- `.github/workflows/release.yml` → build CI/CD automatique GitHub Actions

### Documentation

| Fichier | Contenu |
|---------|---------|
| `README.md` | Vision, stack, install rapide |
| `INSTALL.md` | Install dev + prod détaillée |
| `QUICKSTART.md` | Quickstart local 5 min |
| `DEPLOY.md` | Déploiement client final standard |
| `DEPLOY-OVH.md` | Spécifique OVH (Pro + Mutualisé) |
| `DEPLOY-PERSO-OVH.md` | OVH Perso sans SSH (via GitHub Actions) |
| `ARCHITECTURE.md` | Architecture complète, schéma DB, plugin system |
| `docs/PLUGIN-DEVELOPER.md` | Guide création de plugin tiers |
| `_imports/MAPPING.md` | Mapping WordPress → PraxiTests |
| `_imports/CONVERSION-CHECKLIST.md` | Checklist conversion plugin WP |
| `HANDOFF.md` | **Ce fichier** — reprise conversation |

---

## 5. Décisions en attente / questions ouvertes

### A. Architecture pour decisionpro.fr — Laravel ou pure-PHP ?

**Contexte** : decisionpro existant utilise une **archi pure-PHP custom** (Router, Controllers, Repositories, Services, single install.php 262 KB). Pas Laravel.

**Question** : L'utilisateur (Alexandre) veut que PraxiTests **s'installe comme decisionpro**.

**Deux interprétations** :

1. **Même UX d'installation** : install.php upload + clic = c'est ce qu'on a déjà construit (Laravel + install.php avec auto-DB-create + boot Kernel programmatique)
2. **Même architecture** : refactor PraxiTests en pure-PHP MVC custom (gros chantier)

**Position recommandée** : option 1. La stack Laravel est solide, l'install.php auto-suffit. Ne pas refactorer pour mimer.

**Action** : demander à Alexandre de clarifier en début de prochaine conversation. Référence : il a uploadé `Backup 190426.zip` (decisionpro entier). Ce backup contient `install.php` 262 KB + `manifest.json` (PWA Praxis Accompagnement) + structure pure-PHP MVC.

### B. Sous-domaine final

À choisir : `praxitests.decisionpro.fr` vs `app.decisionpro.fr` vs `tests.decisionpro.fr` vs autre. À configurer dans OVH Manager → Multisite avec **document root = `praxitests/public`** (pas la racine).

### C. Workflow de déploiement choisi

3 options possibles :

1. **SSH OVH Pro direct** — upload source via FTP, SSH, composer install + npm run build, ouvrir install.php
2. **Zip pré-buildé via GitHub Actions** — push code, Actions builde, télécharge zip, FTP, install.php
3. **Zip pré-buildé local** — `make-release.bat` sur machine dev, FTP, install.php (nécessite PHP+Composer+Node localement)

Recommandation : **option 1 (SSH OVH Pro)** car Alexandre a SSH sur Pro et c'est le plus rapide.

### D. Sprints futurs (non-démarrés)

- **Sprint 3 — Production-ready** :
  - Multi-tenant runtime (middleware `IdentifyTenant`, white-label)
  - Stripe/Cashier billing pour `professional_accounts`
  - RGPD complet (export données, droit oubli, anonymisation)
  - Webhooks
  - Newsletter / digest auto
- **Sprint 4 — Marketplace plugins** : UI pour installer plugins tiers via zip upload
- **Sprint 5 — Mobile app PWA** : manifest.json + service worker (decisionpro l'a déjà)
- **Sprint 6 — Analytics avancés** : funnel completion, heatmaps, A/B test reporting

### E. IA — clé API à fournir

Pour activer la synthèse + 15 métiers, il faut une clé API dans `.env` après installation. Drivers disponibles :
- `AI_DEFAULT_DRIVER=anthropic` + `ANTHROPIC_API_KEY=sk-ant-...`
- `AI_DEFAULT_DRIVER=openai` + `OPENAI_API_KEY=sk-...`
- `AI_DEFAULT_DRIVER=mistral` + `MISTRAL_API_KEY=...`
- `AI_DEFAULT_DRIVER=ollama` (local, gratuit, à installer sur serveur)

Sans clé : tests fonctionnent normalement, synthèse IA reste vide.

---

## 6. Backups & ressources sources

| Ressource | Localisation |
|-----------|--------------|
| Code PraxiTests | `C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests` |
| Plugins WP source (5) | `_imports/extracted/Sauvegarde plugins tests/` |
| Backup decisionpro | uploadé `Backup 190426.zip` (951 KB) — extracted dans `/tmp/Backup 190426/` lors de la session |
| Mémoire Claude | `C:\Users\Fradin Alexandre\AppData\Roaming\Claude\local-agent-mode-sessions\.../memory/` (PraxiTests project, OVH hosting) |

---

## 7. Comment reprendre dans une nouvelle conversation

### Étape 1 — Ouvre une nouvelle conversation Claude

### Étape 2 — Donne le contexte initial

Copie-colle ce prompt (ou attache `HANDOFF.md`) :

```
Je reprends le projet PraxiTests. Lis HANDOFF.md à la racine du projet
(C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests\HANDOFF.md)
pour le contexte complet.

Sujet du jour : [DÉCRIRE CE QUE TU VEUX FAIRE]
```

### Étape 3 — Commandes utiles à connaître

```bash
# Vérification rapide
ls plugins/                                    # 5 plugins
cat composer.json | grep -c '"version"'        # version package
cat HANDOFF.md | head -50                      # ce fichier

# Commandes Artisan PraxiTests
php artisan praxitests:plugins:discover --sync
php artisan praxitests:plugins:activate praximet
php artisan praxitests:plugins:activate praxivaleurs
php artisan praxitests:plugins:activate praxicare
php artisan praxitests:plugins:activate praxiemo
php artisan praxitests:plugins:activate praximum
```

### Étape 4 — Liste des hooks plugin (pour développer un nouveau plugin)

**Actions** :
- `plugin.booted`, `plugin.activated`, `plugin.deactivated`
- `profile.completed`
- `attempt.started`, `attempt.answered`, `attempt.completed`
- `ai.synthesis.completed`, `jobs.generated`
- `gamification.xp_awarded`, `gamification.badge_earned`, `gamification.insight_unlocked`
- `campaign.sent`, `insights.generated`

**Filters** :
- `attempt.scoring`, `jobs.suggested`
- `ai.synthesis.messages`, `ai.synthesis.output`, `ai.driver.{name}`
- `email.context`, `gamification.badge.criterion`

---

## 8. Comptes & credentials

### Compte admin par défaut (configurable via `.env` AVANT seeder)

```dotenv
PRAXITESTS_ADMIN_EMAIL=admin@praxitests.local
PRAXITESTS_ADMIN_PASSWORD=changeme123
PRAXITESTS_ADMIN_NAME="Administrateur"
```

⚠ **Changer le mot de passe** après première connexion.

### OVH

- Hébergement : décisionpro.fr (Pro · cluster121)
- IP : 188.165.53.185
- Sous-domaine à créer : à choisir (cf. §5.B)
- DB à créer : via OVH Manager (l'auto-création de l'installeur ne fonctionne pas sur OVH car les users MySQL n'ont pas les droits CREATE DATABASE — le hint dans l'installeur le précise)

---

## 9. Liste complète des tâches accomplies

42 tâches terminées, 3 sprints :

### Sprint 0 — Base Laravel (tasks 1-13)
Architecture, scaffolding, migrations, plugin system, moteur tests, IA, gamification, emailing, frontend, dashboard, installeur web v1, docs, vérif.

### Sprint 1 — Pages manquantes + roles (tasks 14-19)
Auth (Login/Register), Landing, pages Admin (Tests, Plugins, Campaigns, Leads), Spatie roles, seeder admin, vérif.

### Sprint 2 — Plugins WP (tasks 20-30)
Infrastructure import, analyse 5 plugins, conversion chaque plugin, tests Pest, vérif, exact WP questions, archétypes.

### Sprint Test local & déploiement (tasks 31-42)
Pre-install audit, scripts setup auto, autoload composer, QUICKSTART, install.php Laravel-boot, build-release zip, DEPLOY.md, DEPLOY-OVH.md, GitHub Actions, DEPLOY-PERSO-OVH, auto-création DB.

---

## 10. État final du repo (à conserver)

```
PraxiTests/
├── .github/workflows/release.yml          # CI auto-build
├── _imports/                              # Sources WP plugins (référence)
├── app/
│   ├── Console/Commands/                  # Plugin discover/activate
│   ├── Core/                              # AI, Gamification, Mailing, Plugins, TestEngine
│   ├── Http/Controllers/                  # Admin, Auth, Candidate
│   ├── Http/Middleware/                   # HandleInertiaRequests
│   ├── Jobs/                              # GenerateAttemptInsights, SendSequenceStepJob, ExtractCvDataJob
│   ├── Models/                            # 14 modèles Eloquent
│   └── Providers/                         # PraxiTestsServiceProvider, AppServiceProvider
├── bootstrap/                             # app.php, providers.php
├── config/                                # praxitests, plugins, ai, gamification, neuromarketing, permission
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/                        # 10 migrations
│   └── seeders/                           # Database, Roles, AdminUser, Badge, DemoTest
├── docs/PLUGIN-DEVELOPER.md
├── plugins/                               # 5 plugins convertis
│   ├── praxicare/
│   ├── praxiemo/
│   ├── praximet/
│   ├── praximum/
│   └── praxivaleurs/
├── public/
│   ├── index.php                          # Détecte install non terminée → redirige install.php
│   └── install.php                        # Installeur web 7 étapes (431 lignes)
├── resources/
│   ├── css/app.css                        # Tailwind + design tokens
│   ├── js/                                # app.js, Layouts/, Pages/
│   └── views/                             # app.blade.php, mail/, pdf/
├── routes/                                # web, admin, auth, console
├── storage/                               # Avec .gitignore préparés
├── tests/                                 # Pest config + 6 ScoringTest
├── .env.example
├── .gitignore
├── ARCHITECTURE.md
├── DEPLOY.md
├── DEPLOY-OVH.md
├── DEPLOY-PERSO-OVH.md
├── HANDOFF.md                             # ← Ce fichier
├── INSTALL.md
├── QUICKSTART.md
├── README.md
├── artisan
├── composer.json                          # PSR-4 inclut tous les plugins
├── dev.bat / dev.sh                       # Démarrage 3 processus
├── make-release.bat / make-release.sh     # Build zip distribuable
├── package.json
├── phpunit.xml
├── setup.bat / setup.sh                   # Install local
├── tailwind.config.js
└── vite.config.js
```

---

## 11. Avertissement final

Quand tu reprends, **vérifie d'abord l'état réel du repo** avant d'agir : ce document est figé à mai 2026. Si Alexandre a fait des modifs entre temps, le code prime. Commande utile :

```bash
cd "C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests"
git log --oneline -20    # voir derniers commits si Git initialisé
ls -la HANDOFF.md         # vérifier date de ce fichier
find . -type f -newer HANDOFF.md -not -path './.git/*' -not -path './vendor/*' -not -path './node_modules/*' | head -30
```

---

**Bon redémarrage.** 🚀
