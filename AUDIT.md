# AUDIT GLOBAL PRAXIQUEST — Juin 2026

> 5 passes d'analyse indépendantes : sécurité, performance, qualité PHP, frontend, tests.
> **Version :** 1.0.0-alpha — commit `bb7a4a1`

---

## TABLEAU DE BORD

| Passe | Critiques | Élevés | Moyens | Faibles |
|-------|-----------|--------|--------|---------|
| Sécurité | 2 | 5 | 6 | 2 |
| Performance | 0 | 5 | 5 | 2 |
| Qualité PHP | 5 | 4 | 9 | 5 |
| Frontend | 0 | 3 | 4 | 4 |
| Tests | 4 gaps critiques sans couverture | | | |

---

## PASSE 1 — SÉCURITÉ

### 🔴 SEC-01 — `install.php` : guard post-installation bypassable par `?force=1`
**Fichier :** `public/install.php` — ligne 115 | **CRITIQUE**

Le guard `if (file_exists($installFlag) && !isset($_GET['force']))` est contournable par `?force=1`. N'importe qui peut accéder à `https://site.com/install.php?force=1`, wiper la base et rejouer les seeders sans authentification.

**Fix :**
```php
// Remplacer par — aucune exception possible
if (file_exists($installFlag)) {
    http_response_code(403); echo 'Already installed.'; exit;
}
```
Et bloquer `install.php` côté nginx/Apache dès que `.installed` existe.

---

### 🔴 SEC-02 — `install.php` : bloc AJAX exécuté avant le guard
**Fichier :** `public/install.php` — lignes 22–111 | **CRITIQUE**

`if (isset($_GET['ajax']))` est évalué avant le guard. Même si SEC-01 est corrigé, `?ajax=1`, `?ajax=2`, `?ajax=3` déclenchent migrate/seed/plugins directement.

**Fix :** Déplacer le guard en toute première ligne du fichier, avant tout traitement.

---

### 🟠 SEC-03 — Aucun rate limiting sur login / register / forgot-password
**Fichier :** `routes/auth.php` — lignes 8, 11, 17 | **ÉLEVÉE**

Aucun middleware `throttle` sur les endpoints d'authentification → brute-force, énumération d'emails, spam de comptes.

**Fix :**
```php
Route::post('/login',           ...)->middleware('throttle:5,1');
Route::post('/register',        ...)->middleware('throttle:10,60');
Route::post('/forgot-password', ...)->middleware('throttle:3,10');
```

---

### 🟠 SEC-04 — `install.php` : info disclosure (`?force=1` suggéré dans la page)
**Fichier :** `public/install.php` — ligne 117 | **ÉLEVÉE**

La page d'erreur "déjà installé" imprime `Ajouter ?force=1 pour réinstaller` en clair.

**Fix :** Supprimer toute mention de `?force` dans les réponses publiques.

---

### 🟠 SEC-05 — Plugin autoload : `service_provider` non validé (RCE potentiel)
**Fichier :** `app/Core/Plugins/PluginManager.php` — ligne 48 | **ÉLEVÉE**

`$providerClass = $manifest['service_provider']` est passé directement à `new $providerClass($app)`. Un `plugin.json` modifié (accès FTP/SSH compromis) peut pointer vers n'importe quelle classe autoloadée.

**Fix :** Valider dans `PluginManifestValidator` que `service_provider` est un FQCN dans le namespace déclaré du plugin.

---

### 🟠 SEC-06 — `results.inertia_page` non whitelistée
**Fichier :** `app/Http/Controllers/Candidate/ResultController.php` — ligne 20 | **ÉLEVÉE**

La page retournée par le filtre Inertia est rendue sans validation → un plugin malveillant peut exposer une vue arbitraire.

**Fix :**
```php
$allowed = ['Candidate/ResultsShow', 'PraximetResult', 'PraxiCareResult',
            'PraxiEmoResult', 'PraxiMumResult', 'PraxiValeursResult'];
$page = in_array($page, $allowed, true) ? $page : 'Candidate/ResultsShow';
```

---

### 🟠 SEC-07 — XSS stocké dans les emails de campagne
**Fichier :** `resources/views/mail/layouts/campaign.blade.php` — ligne 22 | **ÉLEVÉE**

`{!! $html !!}` rend le body HTML de campagne sans sanitisation. Un admin/pro malveillant peut injecter du JS dans des emails envoyés à tous les utilisateurs.

**Fix :** Sanitiser le HTML via `HTMLPurifier` avant stockage, ou restreindre la création de campagnes au seul rôle `admin`.

---

### 🟡 SEC-08 — `v-html` sur les labels de pagination
**Fichier :** `resources/js/Pages/Admin/Leads/Index.vue` — ligne 78 | **MOYENNE**

`v-html="link.label"` contourne la protection XSS de Vue. Remplacer par `{{ link.label }}`.

---

### 🟡 SEC-09 — `markEmailAsVerified()` inconditionnelle en production
**Fichier :** `app/Http/Controllers/Auth/AuthController.php` — ligne 63 | **MOYENNE**

Pensé comme temporaire ("sans SMTP"), cette ligne restera active si le SMTP n'est jamais configuré, permettant à n'importe qui de s'inscrire avec l'email d'un tiers.

**Fix :** Conditionner à `!config('mail.mailer') || app()->environment('local', 'testing')`.

---

### 🟡 SEC-10 — Injection `.env` par newline dans l'installeur
**Fichier :** `public/install.php` — lignes 128–158 | **MOYENNE**

`app_name`, `app_url` etc. sont écrits dans `.env` sans nettoyage des `\n`. Un retour à la ligne dans `app_name` injecte des clés arbitraires.

**Fix :** `$clean = fn($v) => str_replace(["\n", "\r"], '', $v);` sur toutes les valeurs user.

---

### 🟡 SEC-11 — Filtre `status` sur leads sans whitelist
**Fichier :** `app/Http/Controllers/Admin/LeadController.php` — ligne 16 | **MOYENNE**

`->where('status', $request->string('status'))` sans validation. Ajouter `in_array($status, ['new','contacted','qualified','converted','lost'])`.

---

### 🟡 SEC-12 — `cv_original_name` stocké sans sanitisation
**Fichier :** `app/Http/Controllers/Candidate/OnboardingController.php` — lignes 55, 108 | **MOYENNE**

`getClientOriginalName()` fourni par le client peut contenir `../`, `<script>`, etc.

**Fix :** `basename($request->file('cv')->getClientOriginalName())`.

---

### 🟡 SEC-13 — Rôle `professional` trop permissif (plugins, settings, tests)
**Fichier :** `routes/admin.php` — ligne 11 | **MOYENNE**

Un `professional` peut activer/désactiver des plugins et lire les clés API IA. Restreindre ces routes au rôle `admin` seul.

---

### 🟢 SEC-14 — Validation format clés API insuffisante | **FAIBLE**
### 🟢 SEC-15 — `attemptId` dans la queue sans vérification d'ownership | **FAIBLE**

---

## PASSE 2 — PERFORMANCE

### 🟠 P-07 — Job IA synchrone bloquant sur OVH (20–45 s)
**Fichier :** `app/Http/Controllers/Candidate/AttemptController.php` — ligne 88 | **HAUT**

Avec `QUEUE_CONNECTION=sync`, `GenerateAttemptInsights` s'exécute pendant la requête HTTP. Le timeout PHP OVH (30–60 s) peut couper avant les 240 s configurés → résultat IA perdu silencieusement.

**Fix immédiat :** Passer à `QUEUE_CONNECTION=database` + cron `queue:work` sur OVH.
**Fix intermédiaire :** `dispatch(...)->afterResponse()` pour libérer la response avant l'appel IA.

---

### 🟠 P-08 — `CampaignService::resolveAudience()` charge toute la table users
**Fichier :** `app/Core/Mailing/Services/CampaignService.php` — ligne 74 | **HAUT**

`User::get()` charge tout le modèle. Pour 10 000 users → saturation mémoire.

**Fix :** `User::query()->select(['id','email'])->cursor()` (LazyCollection).

---

### 🟠 P-09 — INSERT `email_logs` ligne par ligne dans la boucle
**Fichier :** `app/Core/Mailing/Services/CampaignService.php` — ligne 42 | **HAUT**

5 000 campagnes = 5 000 INSERT individuels. Accumuler en batch de 500.

---

### 🟠 P-05 — N+1 dans `saveStructure()` : 1 SELECT par section + par question
**Fichier :** `app/Http/Controllers/Admin/TestEditorController.php` — ligne 87 | **HAUT**

5 sections × 10 questions = 51 SELECT dans la transaction.

**Fix :** Pré-charger `TestSection::with('questions')->get()->keyBy('id')` avant la boucle.

---

### 🟠 P-01 — N+1 : `$user->badges()->count()` dans `progressOf()`
**Fichier :** `app/Core/Gamification/GamificationEngine.php` — ligne 87 | **HAUT**

Appelé à chaque affichage de `AttemptController::show()` sans eager load.

**Fix :** Passer `user.badges` dans le `->load()` de `show()` et utiliser `$user->badges->count()`.

---

### 🟡 P-03 — `history()` charge tous les résultats JSON complets en mémoire
**Fichier :** `app/Http/Controllers/Candidate/ResultController.php` — ligne 54 | **MOYEN**

`with('result')` charge `scoring`, `ai_synthesis`, `suggested_jobs` entiers alors que seuls `has_result`, `ai_ready`, `jobs_count` sont utilisés.

**Fix :** `with(['result:attempt_id,ai_synthesis,suggested_jobs'])` + sélection de colonnes + pagination.

---

### 🟡 P-04 + P-11 — 7 COUNT séparés sur le dashboard admin
**Fichier :** `app/Http/Controllers/Admin/DashboardController.php` | **MOYEN**

7 requêtes COUNT à chaque visite → 1 requête agrégée + `Cache::remember(60)`.

---

### 🟡 P-13 — Index incomplet sur `test_attempts`
**Migration :** `2026_04_27_000005` | **MOYEN**

`WHERE user_id = ? AND test_id = ? AND status = ?` n'est couvert que sur 2 colonnes.

**Fix :** `$table->index(['user_id', 'test_id', 'status'])`.

---

### 🟡 P-14 — Aucun index sur `email_sequences(trigger_event, enabled)`
**Migration :** `2026_04_27_000007` | **MOYEN**

Ajout : `$table->index(['trigger_event', 'enabled'])`.

---

### 🟢 P-06 — `firstOrCreate` redondant dans `GamificationEngine` | **MOYEN**
### 🟢 P-15 — `Setting::get()` sans cache | **FAIBLE**
### 🟢 P-10 — Boucle SELECT dans `TestNormsSeeder` | **FAIBLE**

---

## PASSE 3 — QUALITÉ PHP

### 🔴 QC-13 — Race condition sur `GamificationEngine::awardXp()` (double incrément)
**Fichier :** `app/Core/Gamification/GamificationEngine.php` — lignes 22–24 | **CRITIQUE**

Pattern read-then-write non atomique. Deux jobs simultanés lisent le même `xp_total` → l'un des deux incréments est perdu.

**Fix :** `GamificationProgress::where(...)->increment('xp_total', $amount)` (UPDATE atomique SQL).

---

### 🔴 QC-14 — Race condition sur création de `TestAttempt`
**Fichier :** `app/Core/TestEngine/TestEngine.php` — ligne 30 | **CRITIQUE**

Double-clic ou deux requêtes simultanées créent deux attempts. Pas de contrainte DB ni de transaction autour du check + create.

**Fix :** Envelopper dans `DB::transaction()` avec `lockForUpdate()` sur la vérification.

---

### 🔴 QC-24 — `BigFiveScoringEngine::tToPct()` : approximation linéaire incorrecte
**Fichier :** `plugins/praximum/src/Scoring/BigFiveScoringEngine.php` — lignes 128–133 | **CRITIQUE (psychométrique)**

`(T - 20) × 100 / 60` donne une droite. La vraie distribution normale donne T=60 → ~84%, pas 67%. `NormInterpreter::fromTScore()` utilise la vraie CDF — les deux coexistent avec des résultats contradictoires.

**Fix :** Supprimer `tToPct()`, utiliser `NormInterpreter::fromTScore($T)['percentile']` partout.

---

### 🔴 QC-19 — Clé `'sd'` dans `Catalog::normes()` vs `'std_dev'` dans `NormInterpreter`
**Fichier :** `plugins/praximum/src/Scoring/BigFiveScoringEngine.php` — ligne 57 | **CRITIQUE (incohérence)**

Deux systèmes utilisent des clés différentes pour la même donnée → résultats erronés si `NormInterpreter::enrich()` est appliqué aux scores BigFive.

**Fix :** Uniformiser en `'std_dev'` dans `Catalog::normes()`.

---

### 🔴 QC-20 — `EqiScoringEngine::desirabilite()` : logique de biais inversée
**Fichier :** `plugins/praxiemo/src/Scoring/EqiScoringEngine.php` — ligne 89 | **CRITIQUE (psychométrique)**

`if ($sum <= 12)` → "Biais fort". Un score bas peut signifier absence de biais selon la direction des items. Vérifier si la condition devrait être `>= 22`.

**Fix :** Auditer la direction des 6 items DS et corriger la condition.

---

### 🟠 QC-01 — `AnthropicDriver::chat()` retourne vide silencieusement
**Fichier :** `app/Core/AI/Drivers/AnthropicDriver.php` — ligne 57 | **HAUTE**

`return $data['content'][0]['text'] ?? ''` : si l'API retourne un payload inattendu, la synthèse est persistée vide sans erreur.

**Fix :** Lever `\RuntimeException("Empty AI response")` si le texte est absent ou vide.

---

### 🟠 QC-10 — `$attempt->user` peut être null dans `AttemptController::show()`
**Fichier :** `app/Http/Controllers/Candidate/AttemptController.php` — ligne 64 | **HAUTE**

Si l'utilisateur est soft-deleted, `$attempt->user` → null → `progressOf(null, ...)` → TypeError.

**Fix :** Charger `'user'` dans le `with()` et ajouter `abort_unless($attempt->user, 404)`.

---

### 🟠 QC-12 — `ArchetypeResolver` : `file_get_contents` sans vérification d'existence
**Fichier :** `plugins/praximum/src/Archetypes/ArchetypeResolver.php` — ligne 22 | **HAUTE**

Fichier absent ou corrompu → `json_decode(false, true)` → `null` → `?: []` cache silencieusement le problème. Tous les scores BigFive retournent `archetype: null`.

**Fix :** `if (!file_exists($path)) throw new \RuntimeException("archetypes.json manquant")`.

---

### 🟠 QC-05 — `saveStructure()` : 80+ lignes de logique métier dans un controller
**Fichier :** `app/Http/Controllers/Admin/TestEditorController.php` — lignes 63–146 | **HAUTE**

**Fix :** Extraire `TestStructureService::save(Test $test, array $data): void`.

---

### 🟡 Autres qualité (résumé)

| ID | Fichier | Description |
|----|---------|-------------|
| QC-02 | `ExtractCvDataJob` | Fail silencieux si profil introuvable |
| QC-04 | `SequenceRunner` | Return sans log sur séquence/user introuvable |
| QC-06 | `BigFiveScoringEngine::score()` | 100 lignes, à décomposer |
| QC-11 | `NarrativeEngine` | `$attempt->test` non null-safé |
| QC-15 | `AttemptController::start()` | Mise à jour invitation dans le controller |
| QC-16 | `AuthController::register()` | Token d'invitation à extraire en service |
| QC-17 | `EqiScoringEngine` | Indices questions DS hardcodés (80–85) |
| QC-18 | `BigFiveScoringEngine` | Norme fallback hardcodée silencieuse |
| QC-23 | `BigFiveScoringEngine::computeT()` | `int $brut` → truncate si float futur |
| QC-25 | `ArchetypeResolver` | Retour null non géré dans `score()` |

---

## PASSE 4 — FRONTEND VUE / INERTIA

### 🟠 FE-01 — `AttemptPlay.vue` : crash sur test vide (0 questions)
**Sévérité : HAUTE**

Si `attempt.test.sections = []`, `allQuestions = []`, `currentIndex = ref(-1)`, `currentQuestion.prompt` → TypeError → page blanche.

**Fix :**
```html
<div v-if="!totalQuestions" class="pt-card p-8 text-center text-slate-500">
    Ce test ne contient aucune question.
</div>
<template v-else>…</template>
```

---

### 🟠 FE-02 — `AttemptPlay.vue` : double soumission possible
**Sévérité : HAUTE**

Le bouton "Suivant" n'est pas désactivé pendant l'envoi Inertia → double-clic soumet deux fois la même réponse.

**Fix :** `ref isSubmitting` + `:disabled="isSubmitting"` sur le bouton.

---

### 🟠 FE-03 — `pt-btn-primary` / `pt-btn-ghost` : `focus:outline-none` efface le focus visible
**Fichier :** `resources/css/app.css` — lignes 86, 105 | **HAUTE (accessibilité)**

`focus:outline-none` a une spécificité plus haute qu