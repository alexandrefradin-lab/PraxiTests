# Checklist de conversion par plugin

À cocher pendant la conversion de chaque plugin WordPress.

## Plugin : `____________________`

### Étape 1 — Analyse
- [ ] Décompressé dans `_imports/extracted/{slug}/`
- [ ] Lu le header PHP du plugin (Name, Version, Author, Description)
- [ ] Listé les hooks WP utilisés
- [ ] Listé les shortcodes / pages admin
- [ ] Identifié les tables custom WP (si présentes)
- [ ] Identifié les dépendances (ACF, WooCommerce, autres)
- [ ] Récupéré les questions + options (en JSON ou tableau PHP)
- [ ] Récupéré la logique de scoring (formule + dimensions)
- [ ] Récupéré le format de restitution

### Étape 2 — Génération scaffold
- [ ] Créé `plugins/{slug}/`
- [ ] Écrit `plugin.json` valide
- [ ] Écrit `PluginServiceProvider.php` avec hooks `onActivate` / `onDeactivate`
- [ ] Migration créée pour le test (si scoring spécifique nécessite tables custom)
- [ ] Seeder questions

### Étape 3 — Logique métier
- [ ] `ScoringEngine` custom implémenté + enregistré
- [ ] Tests unitaires Pest pour le scoring (au moins 3 cas : profil A, B, C)
- [ ] Hooks PraxiQuest branchés (`attempt.completed`, `jobs.suggested`, etc.)

### Étape 4 — UI / Design
- [ ] Pages Vue créées sous `plugins/{slug}/resources/js/Pages/`
- [ ] Layout candidat / admin utilisé (jamais de layout custom)
- [ ] Classes design system uniquement (`pt-card`, `pt-btn-primary`, `pt-input`, `pt-badge`, `pt-progress-fill`)
- [ ] Aucune couleur custom (gradient indigo→emerald respecté)
- [ ] Police Inter par défaut
- [ ] Restitution réutilise composant `Candidate/ResultsShow.vue` si possible

### Étape 5 — Routes / Admin
- [ ] Routes plugin déclarées dans `routes/plugin.php`
- [ ] Page admin de config si nécessaire
- [ ] Permissions Spatie déclarées dans `plugin.json`

### Étape 6 — Polish
- [ ] `README.md` plugin écrit (install, config, hooks exposés)
- [ ] `npm run build` passe sans warning
- [ ] `php artisan praxiquest:plugins:discover --sync` détecte le plugin
- [ ] Test end-to-end manuel : passer le test → voir résultats

### Étape 7 — Validation
- [ ] Tests Pest passent
- [ ] Conversion validée par l'utilisateur (Alexandre)
- [ ] Plugin déplacé hors `_imports/` (le source WP peut être archivé)
