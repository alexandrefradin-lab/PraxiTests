# HANDOFF — PraxiQuest

> Document de reprise pour continuer le projet dans une nouvelle conversation Claude.

---

## 1. Mission

**PraxiQuest** = SaaS propriétaire d'évaluation et orientation professionnelle augmenté par 3 leviers :

1. **IA** — synthèse profil + 15 métiers (drivers Anthropic / OpenAI / Mistral / Ollama)
2. **Neuromarketing** — optimisation conversion (8 biais cognitifs)
3. **Gamification** — XP, niveaux, badges, narration, insights débloquables

**Architecture plugin-first** : tout ce qui peut bouger est en plugin.

**Cible** : multi-flexible (B2C candidat / B2B RH / B2B2C écoles & orga).

---

## 2. Stack technique

| Couche | Tech |
|--------|------|
| Backend | PHP 8.2 + Laravel 11 |
| Frontend | Inertia.js + Vue 3 + Vite |
| Styling | Tailwind CSS + design system custom |
| DB | MySQL 8 / PostgreSQL 15 |
| Cache/Queue | Redis |
| IA | driver abstraction multi-LLM |

---

## 3. Workspace local

```
C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiQuest
```

---

## 4. GitHub

- **Repo** : `https://github.com/alexandrefradin-lab/PraxiQuest` (privé)
- **Branch** : `main`
- **GitHub Actions** : build automatique à chaque push → zip complet (vendor + public/build) téléchargeable dans Actions → Artifacts

### Workflow de mise à jour (à répéter à chaque modif)

```powershell
# Dans PowerShell, depuis le dossier PraxiQuest :
git add .
git commit -m "Description du changement"
git push
# → GitHub Actions build le zip automatiquement (~2 min)
# → Télécharger le zip depuis GitHub Actions → Artifacts
# → Extraire avec 7-Zip vers C:\pt\
# → FileZilla → upload dans www/praxiquest/
```

---

## 5. Hébergement OVH

- **Plan** : Pro — decisionpro.fr — cluster121
- **IP** : 188.165.53.185

### Déploiement (workflow définitif, sans SSH)

1. Modifier le code en local
2. `git push` → GitHub Actions build le zip
3. Télécharger zip depuis GitHub Actions → Artifacts
4. **Extraire avec 7-Zip vers `C:\pt\`** (chemin court obligatoire sur Windows)
5. FileZilla → upload dans `www/praxiquest/`
6. Première install : ouvrir `https://praxiquest.decisionpro.fr/install.php`
7. Mise à jour : les fichiers remplacent les anciens, pas besoin de réinstaller

### Sous-domaine OVH

- URL cible : `https://praxiquest.decisionpro.fr`
- Document root : `praxiquest/public` (**pas** `praxiquest/`)
- À créer dans OVH Manager → Multisite si pas encore fait

### Base de données OVH

- Créer dans OVH Manager → Bases de données
- Hôte : `XXXXX.mysql.db` (**jamais** `localhost`)
- Port : 3306

---

## 6. Installeur web

- URL : `https://praxiquest.decisionpro.fr/install.php`
- Style : single form → résultats (inspiré DecisionPro)
- Se verrouille après install via `storage/app/.installed`
- Pour réinstaller : supprimer `storage/app/.installed` ou ajouter `?force=1`

---

## 7. Ce qui est livré

### Core Laravel
- Auth Sanctum + sessions web
- 10 migrations : users, profiles, plugins, tests, sections, questions, attempts, answers, results, invitations, gamification, emails, leads, pro_accounts, audit_logs, jobs, permissions
- 14 modèles Eloquent
- Controllers Candidate + Admin + Auth
- Roles Spatie : admin / professional / candidate (13 permissions)
- Seeder admin configurable via `.env`

### Plugin system
- `PluginContract` + `AbstractPlugin` + `PluginManager` + `PluginRegistry` + `PluginHooks`
- Auto-discovery `/plugins/*/plugin.json`
- Commandes Artisan : `praxiquest:plugins:discover --sync`, `praxiquest:plugins:activate {slug}`

### IA
- `AIManager` + 4 drivers (Anthropic, OpenAI, Mistral, Ollama)
- `ProfileSynthesisService` + `JobSuggestionService` (15 métiers) + `CvExtractionService`
- Job async `GenerateAttemptInsights`

### Gamification
- `GamificationEngine` + `BadgeEvaluator` + `NarrativeEngine`
- 5 badges seedés

### Neuromarketing + Emailing
- `NeuromarketingOptimizer` (8 biais)
- `CampaignService` + `SequenceRunner`

### 5 plugins convertis depuis WordPress

| Slug | Test | Questions | Scoring |
|------|------|-----------|---------|
| `praximet` | RIASEC | 84 | code Holland 3 lettres |
| `praxivaleurs` | Valeurs Schwartz | 40 | top 5 valeurs |
| `praxicare` | Karasek + MBI | 48 | 5 profils |
| `praxiemo` | Intelligence émotionnelle | 80+6 | EQ-i 16 dim |
| `praximum` | Big Five OCEAN | 128 | T normé · 30 facettes · 16 archétypes |

### Frontend Vue (19 pages)
- Layouts : CandidateLayout, AdminLayout, AuthLayout
- Candidate : Onboarding, TestsIndex, AttemptPlay, ResultsShow
- Admin : Dashboard, Tests/Index, Tests/Edit, Plugins/Index, Plugins/Show, Campaigns/Index, Campaigns/Edit, Leads/Index, Leads/Show
- Auth : Login, Register
- Public : Landing
- 5 pages résultats plugins

---

## 8. Compte admin par défaut

```dotenv
PRAXIQUEST_ADMIN_EMAIL=admin@praxiquest.local
PRAXIQUEST_ADMIN_PASSWORD=changeme123
```

⚠ Changer après première connexion.

---

## 9. IA — activation post-install

Édite `.env` sur le serveur (via FTP) :

```dotenv
AI_DEFAULT_DRIVER=anthropic
ANTHROPIC_API_KEY=sk-ant-xxxxx
ANTHROPIC_MODEL=claude-sonnet-4-6
```

Sans clé : les tests fonctionnent, synthèse IA reste vide.

---

## 10. Sprints futurs (non démarrés)

- **Sprint 3** : Multi-tenant, Stripe/Cashier billing, RGPD complet, webhooks
- **Sprint 4** : Marketplace plugins (upload zip via UI)
- **Sprint 5** : PWA mobile (manifest + service worker)
- **Sprint 6** : Analytics avancés (funnel, heatmaps, A/B reporting)

---

## 11. Problèmes résolus (session mai 2026)

| Problème | Solution |
|----------|----------|
| Wizard 7 étapes trop complexe | Installeur single-form style DecisionPro |
| Déploiement sans SSH | GitHub Actions → zip → FileZilla |
| Pest v2 vs PHPUnit 11 | Migré pest v3 |
| Ziggy import Vite cassé | Import `ziggy-js` direct, alias supprimé |
| `Head` non enregistré Vue | `.component('Head', Head)` global dans app.js |
| `{{ }}` dans template Vue | Remplacé par `v-pre` |
| Chemin trop long Windows | Extraire avec 7-Zip vers `C:\pt\` |

---

## 12. Comment reprendre

Attache ce fichier dans une nouvelle conversation et dis :

```
Je reprends le projet PraxiQuest. Lis HANDOFF.md pour le contexte.
Sujet du jour : [DÉCRIRE CE QUE TU VEUX FAIRE]
```

Active caveman mode en début de session : `/caveman`
