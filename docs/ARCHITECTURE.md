# PraxiQuest — Architecture

> SaaS propriétaire d'évaluation et orientation. Plugin-first. IA + neuromarketing + gamification.

## 1. Stack technique

| Couche | Tech | Rôle |
|--------|------|------|
| Backend | PHP 8.3 + Laravel 11 | Framework principal |
| Frontend | Inertia.js + Vue 3 + Vite | SPA sans API séparée |
| Styling | Tailwind CSS 3 | Design system |
| DB | MySQL 8 ou PostgreSQL 15 | Persistance |
| Cache/Queue | Redis | Sessions, queue, broadcasting |
| Mail | SMTP / Mailgun / SES (configurable) | Transactionnel + campagnes |
| Storage | Local / S3 / Wasabi (configurable) | Uploads CV, exports |
| IA | Driver abstraction (OpenAI, Anthropic, Mistral, local Ollama) | Synthèse + 15 métiers |
| Auth | Laravel Sanctum + sessions | Web + API plugins |
| Search | Laravel Scout + Meilisearch (optionnel) | Recherche métiers/profils |

## 2. Arborescence projet

```
praxiquest/
├── app/
│   ├── Console/Commands/         # CLI (plugin:install, etc.)
│   ├── Core/                     # Code core (séparé du framework)
│   │   ├── Plugins/              # Plugin manager, registry, hooks
│   │   ├── TestEngine/           # Moteur tests + scoring
│   │   ├── AI/                   # Drivers IA + prompt builder
│   │   ├── Gamification/         # Badges, XP, progression
│   │   ├── Neuromarketing/       # Optimisations conversion
│   │   ├── Mailing/              # Campagnes + séquences
│   │   └── Installer/            # Wizard install
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Providers/
│   ├── Services/
│   └── Events/
├── bootstrap/
├── config/
│   ├── praxiquest.php           # Config principale
│   ├── plugins.php              # Config plugin system
│   ├── ai.php                   # Drivers IA
│   └── gamification.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── plugins/                     # Plugins tiers (auto-discovery)
│   └── exemple-test-mbti/
│       ├── plugin.json
│       ├── PluginServiceProvider.php
│       ├── src/
│       ├── resources/
│       └── routes/
├── public/
│   ├── install.php              # Installeur standalone
│   └── index.php
├── resources/
│   ├── js/
│   │   ├── Pages/               # Inertia pages
│   │   ├── Components/
│   │   ├── Layouts/
│   │   └── Composables/
│   ├── views/                   # Blade (mails, install)
│   └── css/
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── admin.php
│   └── installer.php
├── storage/
├── tests/
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
├── tailwind.config.js
├── README.md
└── INSTALL.md
```

## 3. Modèle de données (entités principales)

```
users (id, email, password, role, locale, created_at)
profiles (user_id, status, status_since, cv_path, cv_extracted_text, metadata json)
plugins (slug, name, version, enabled, config json, installed_at)

tests (id, plugin_slug, slug, name, type, scoring_engine, gamification json, neuromarketing json)
test_sections (test_id, order, title, narrative_text)
test_questions (section_id, type, prompt, options json, weight)

test_invitations (id, professional_id, email, token, status, sent_at, started_at, completed_at)
test_attempts (id, user_id, test_id, invitation_id, status, current_section, started_at, completed_at, time_spent)
test_answers (attempt_id, question_id, value json, time_spent)
test_results (attempt_id, scoring json, ai_synthesis text, suggested_jobs json, generated_at)

gamification_progress (user_id, test_id, xp, level, current_step)
badges (slug, name, description, icon, criteria json)
user_badges (user_id, badge_slug, earned_at)

email_campaigns (id, name, subject, body, audience_filter json, scheduled_at)
email_sequences (id, name, trigger_event, steps json)
email_logs (id, sequence_id, user_id, step, status, opened_at, clicked_at)

leads (id, email, source, score, status, professional_id, metadata json)
professional_accounts (id, user_id, company_name, plan, trial_ends_at)

audit_logs (id, user_id, action, resource, metadata, created_at)
```

## 4. Plugin system

### Contrat plugin (`plugin.json`)

```json
{
  "slug": "test-mbti",
  "name": "Test MBTI",
  "version": "1.0.0",
  "author": "Praxis",
  "type": "test",
  "requires": { "praxiquest": ">=1.0.0" },
  "provides": ["test", "scoring"],
  "service_provider": "Praxis\\TestMbti\\PluginServiceProvider",
  "permissions": ["read:profiles", "write:results"],
  "settings_view": "test-mbti::settings"
}
```

### Hooks (actions + filters style WordPress)

```php
PluginHooks::action('attempt.completed', $attempt);
$jobs = PluginHooks::filter('jobs.suggested', $jobs, $profile);
```

### Auto-discovery

`PluginManager::discover()` scan `/plugins/*/plugin.json`, charge service providers actifs en boot.

### API plugin

Plugin peut :
- enregistrer routes (préfixe `/plugin/{slug}`)
- enregistrer pages admin (menu + vues)
- enregistrer types de tests + scoring engines
- s'abonner à events (action/filter hooks)
- enregistrer drivers IA, mail, storage
- migrer ses propres tables (préfixe `plugin_{slug}_`)

## 5. Couches transversales

### IA — driver abstraction

```
AI\Contracts\Driver
  - generate(prompt, options): string
  - chat(messages, options): string

AI\Drivers\OpenAiDriver
AI\Drivers\AnthropicDriver
AI\Drivers\MistralDriver
AI\Drivers\OllamaDriver
```

`AI::driver('anthropic')->generate($prompt)`. Switch via config.

### Gamification

`GamificationEngine` :
- `awardXp(user, amount, reason)`
- `checkBadges(user, event)`
- `getProgress(user, test)`
- Configurable par test via `gamification` json

### Neuromarketing

`NeuromarketingOptimizer` :
- A/B test variants (subject lines, CTAs)
- Inject biais : urgence, scarcity, social proof, anchoring
- Personnalisation selon profil
- Tracking conversions

### Emailing

`MailCampaign` + `MailSequence` :
- Templates Blade
- Variables dynamiques (profil, résultats, suggestions)
- Triggers : `attempt.completed`, `lead.captured`, `inactive_7days`
- Optimisations neuromarketing par défaut

## 6. Multi-tenant flexible

Single DB multi-tenant via `professional_accounts` + middleware `IdentifyTenant`. Chaque pro a :
- ses tests configurés
- ses templates email
- son white-label (logo, couleurs, domaine)
- ses leads isolés
- ses seats utilisateurs

## 7. Installeur web (wizard)

`/public/install.php` standalone PHP, lance avant Laravel :
1. Check requirements (PHP version, extensions, perms)
2. Config DB (host, port, name, user, pass) — test connexion
3. Compte admin (email, password)
4. License (key, validation API)
5. Branding (nom plateforme, logo)
6. Mail config (SMTP/test envoi)
7. Génère `.env`, run `migrate --seed`, crée fichier `.installed`
8. Redirige vers dashboard

Auto-désactive après install (`.installed` flag bloque `/install.php`).

## 8. Sécurité

- RGPD : consentement explicite, anonymisation, droit oubli, export données
- CV chiffrés au rest (storage encryption)
- Rate limiting auth + API
- 2FA admin obligatoire
- CSRF + XSS Laravel natif
- Plugins sandboxés (permissions explicites)
- Audit log toutes actions sensibles

## 9. Conventions

- PHP : PSR-12, Laravel Pint
- JS : ESLint + Prettier
- Commits : Conventional Commits
- Tests : Pest PHP + Vitest
- Branches : `main`, `develop`, `feature/*`, `release/*`
- CI : GitHub Actions (lint + test + build)

## 10. Roadmap MVP

| Phase | Contenu |
|-------|---------|
| 0 — Base | Scaffolding, DB, auth, profil, installeur |
| 1 — Tests | Moteur tests, plugin system, 1 test exemple |
| 2 — IA | Driver IA, synthèse + 15 métiers |
| 3 — Gamif | Badges, XP, progression, narration |
| 4 — Email | Campagnes, séquences, neuromarketing |
| 5 — Admin | Dashboard pro, scoring leads, stats |
| 6 — Polish | Multi-tenant, white-label, RGPD, docs |
