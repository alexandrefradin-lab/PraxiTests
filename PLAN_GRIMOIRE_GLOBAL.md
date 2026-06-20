# Plan d'implémentation — Le Grimoire global (relecture transversale)

> Objectif : ajouter une **relecture globale** qui croise *tous* les tests complétés du
> candidat pour produire (1) une synthèse transversale et (2) 15 « Voies Possibles »
> consolidées — en plus des synthèses par test qui restent inchangées.
>
> Décisions validées : **régénération automatique à chaque test terminé** ;
> périmètre de cette étape = **plan détaillé, sans code**.

---

## 1. État actuel (rappel)

Tout est **par tentative** aujourd'hui :

| Élément | Fichier | Portée |
|---|---|---|
| Déclenchement | `AttemptController::complete()` → `GenerateAttemptInsights::dispatch($id)->afterResponse()` | 1 tentative |
| Synthèse | `ProfileSynthesisService::synthesize($attempt)` | 1 test |
| 15 métiers | `JobSuggestionService::suggest($attempt)` | 1 test |
| Prompts | `PromptBuilder::profileSynthesis()` / `jobSuggestions()` | reçoivent **un seul** `$attempt` |
| Stockage | `test_results` (1 ligne / tentative) : `ai_synthesis`, `suggested_jobs`, `scoring`, `ai_metadata`… | 1 test |
| Restitution | `ResultController::show()` → `/results/{attempt}` | 1 test |
| Historique | `ResultController::history()` → `/history` | liste brute |
| Partage public | `ProfileShareController::show()` | **dernière** tentative seulement |

➡️ Aucune brique ne croise plusieurs tests. Le Grimoire global est donc une **nouvelle
couche** qui agrège, sans toucher au flux par test (rétro-compatible).

---

## 2. Décision « Voies Possibles » du Grimoire

**Recommandation retenue : consolidation transversale par IA, en un seul appel.**

Pourquoi pas le simple re-classement des métiers déjà suggérés :
- les `fit_score` ne sont **pas comparables** d'un test à l'autre (échelles/critères différents) ;
- chaque test propose ses métiers en isolation → un re-classement ne *croise* rien
  (or c'est tout l'intérêt du Grimoire : « tu es analytique *et* tu valorises l'autonomie *et*
  ton EQ est élevé → voici des pistes qu'aucun test seul n'aurait identifiées »).

Pourquoi un **appel IA unique** (et non deux comme par test) :
- la régénération est **automatique à chaque test fini** → on minimise le coût/la latence ;
- on demande à l'IA un **seul JSON** contenant `{ synthese, voies[] }`. Une passe, ~1 appel.

➡️ On garde les métiers par test affichés dans chaque `/results/{attempt}` ; le Grimoire
ajoute *sa propre* liste consolidée.

---

## 3. Modèle de données

Nouvelle table `profile_grimoires` (une ligne par utilisateur, mise à jour en place).

```
Migration : database/migrations/XXXX_create_profile_grimoires_table.php

profile_grimoires
├── id
├── user_id            (FK users, unique, cascade on delete)
├── synthesis          (longText, nullable)      ← texte transversal
├── voies              (json, nullable)          ← 15 Voies consolidées
├── tests_included     (json, nullable)          ← [attempt_id => {test, completed_at}] tracés
├── tests_signature    (string, nullable)        ← empreinte (cf. §5) pour éviter régénérations inutiles
├── ai_driver, ai_model, ai_tokens_used          ← traçabilité (mêmes champs que test_results)
├── ai_metadata        (json, nullable)          ← {prompt_version, dimensions, tests_count…}
├── status             (enum: pending|ready|failed, default pending)
├── generated_at       (timestamp, nullable)
└── timestamps
```

Choix « 1 ligne / user mise à jour » plutôt qu'historisée : le Grimoire est une vue
*courante* du profil. (Si tu veux garder l'historique des versions plus tard, on ajoutera
une table `profile_grimoire_versions` ; hors périmètre ici.)

**Modèle** `app/Models/ProfileGrimoire.php` : `$fillable` explicite (cf. règle #3 du projet),
casts `voies/tests_included/ai_metadata => array`, `generated_at => datetime`,
relation `belongsTo(User)`. Sur `User` : `hasOne(ProfileGrimoire::class)` + helper
`grimoire()` qui crée la ligne si absente (`firstOrCreate`).

---

## 4. Couche IA

### 4.1 `PromptBuilder::globalGrimoire(User $user, Collection $attempts): array`
Nouvelle méthode. Construit **un** prompt à partir de *tous* les `attempts` complétés :

- réutilise `enrichScoringForPrompt()` (déjà existant) pour **chaque** test → labels qualitatifs ;
- assemble un contexte :
  ```
  {
    profil: { statut, depuis, rôle, industrie, cv_extrait },   // 1× (User.profile)
    tests: [
      { nom, type, interprétation_par_dimension, synthèse_test },  // 1 entrée / test
      ...
    ]
  }
  ```
- system prompt : même posture que `profileSynthesis` (consultant orientation senior,
  FR, bienveillant, pas de chiffres, pas de conseils médicaux/juridiques/financiers),
  + consigne explicite « **croise les tests entre eux**, ne répète pas les synthèses
  individuelles, mets en évidence convergences et tensions ».
- sortie demandée : **JSON strict**
  ```json
  { "synthese": "…400-600 mots, 3-4 paragraphes…",
    "voies": [ { "titre":"", "secteur":"", "fit_score":0,
                 "pourquoi":"", "appui_tests":["RIASEC","Big Five"],
                 "prochaine_etape":"" }, … ] }   // exactement N (config, défaut 15)
  ```
  `appui_tests` est nouveau : indique **quels tests** soutiennent la piste (valeur ajoutée
  visible du croisement).
- réutiliser `sanitizeUserContent()` sur le CV (déjà fait dans `profileSynthesis`).

### 4.2 `GlobalGrimoireService` (`app/Core/AI/Services/GlobalGrimoireService.php`)
Namespace `Praxis\Core\AI\Services` (cohérent avec les deux services existants).
- `generate(User $user): ProfileGrimoire`
- charge les attempts `completed` + `result` ; si **0** test → ne génère pas (cf. §8) ;
- appelle `AIManager::forTask('global_grimoire')` ;
- parse le JSON (réutiliser la logique `parseJson()` de `JobSuggestionService` — à
  factoriser dans un trait `ParsesAiJson` partagé par les deux services) ;
- écrit la ligne `profile_grimoires` (synthesis, voies, tests_included, signature,
  ai_*, metadata, status=ready, generated_at) ;
- hooks plugins : `PluginHooks::applyFilters('ai.grimoire.messages'…)`,
  `applyFilters('ai.grimoire.output'…)`, `doAction('ai.grimoire.completed', $user, $grimoire)`
  (symétrie avec l'existant → les plugins peuvent surcharger).

### 4.3 `config/ai.php` → ajouter la tâche
```php
'global_grimoire' => [
    'driver' => null,                 // = défaut (anthropic / claude-sonnet)
    'prompt_version' => '1.0',
    'count' => 15,
],
```

---

## 5. Régénération automatique + anti-gaspillage

### 5.1 Job `GenerateGlobalGrimoire` (`app/Jobs/`)
Calqué sur `GenerateAttemptInsights` :
- `ShouldQueue, ShouldBeUnique`, `uniqueId() = "grimoire_user_{userId}"` ;
- `tries=3`, `timeout=240` ;
- `handle(GlobalGrimoireService $svc)` : recharge le user, vérifie la **signature**
  (cf. 5.3) ; si inchangée → skip (log) ; sinon `$svc->generate($user)` ;
- `catch Throwable` → `writeFallback()` : status=`failed` + message de repli, pour ne
  jamais bloquer la page (même philosophie que l'existant).

### 5.2 Déclenchement (point d'accroche)
Dans **`GenerateAttemptInsights::handle()`**, après `$synthesis` + `$jobs` réussis
(juste avant ou après `doAction('insights.generated')`), enchaîner :
```php
GenerateGlobalGrimoire::dispatch($attempt->user_id)->afterResponse();
```
Pourquoi là et pas dans `AttemptController::complete()` : le Grimoire doit lire la
synthèse **par test** qui vient d'être produite ; on le lance donc *après* que les
insights du test soient en base. Sur OVH `QUEUE_CONNECTION=sync`, `afterResponse()`
garde la requête HTTP rapide ; le candidat voit d'abord `/results/{attempt}`, le
Grimoire se finalise en arrière-plan et affiche son propre état `pending`.

### 5.3 Signature anti-régénération
`tests_signature = md5(json(attempt_id + result.generated_at triés))`.
Avant de rappeler l'IA, le job compare la signature courante à `profile_grimoires.tests_signature` ;
identiques → skip. Protège contre : re-complétion d'un même test, double-dispatch,
queues sync OVH où `ShouldBeUnique` ne bloque plus après exécution (même piège déjà
documenté dans `GenerateAttemptInsights`).

---

## 6. Route + Controller

### `routes/web.php` (dans le groupe `auth`)
```php
Route::get('/grimoire',          [GrimoireController::class, 'show'])->name('grimoire.show');
Route::get('/grimoire/status',   [GrimoireController::class, 'status'])->name('grimoire.status'); // polling
Route::post('/grimoire/refresh', [GrimoireController::class, 'refresh'])->name('grimoire.refresh'); // bouton régénérer
Route::get('/grimoire/pdf',      [GrimoireController::class, 'pdf'])->name('grimoire.pdf');         // optionnel
```
On obtient enfin une vraie URL `/grimoire` (colle au `DESIGN_BRIEF_PRAXIQUEST.md`,
section « Le Grimoire »).

### `GrimoireController`
- `show()` : charge `user->grimoire` + la liste des tests complétés (pour la colonne
  « tes épreuves »). Si aucun test → page « vide » incitative (lien `/tests`).
  Si grimoire absent/`pending` → rend la page avec `ai_pending=true` (la Vue poll
  `/grimoire/status`, exactement comme `ResultsShow.vue` poll `/results/{id}/status`).
- `status()` : JSON `{ ready: status==='ready', failed: status==='failed' }`.
- `refresh()` : invalide la signature + `GenerateGlobalGrimoire::dispatch(userId)` →
  back(). (Garde-fou anti-spam : throttle/ cooldown ex. 1×/min.)
- `pdf()` (optionnel) : réutilise le pipeline DomPDF existant avec une vue dédiée
  `pdf/grimoire.blade.php` et `pdfOptions()` (à extraire de `ResultController` vers un
  trait `BuildsBrandedPdf` pour ne pas dupliquer).

---

## 7. Front (Inertia / Vue)

`resources/js/Pages/Candidate/Grimoire.vue` :
- props : `grimoire` (synthesis, voies, generated_at, status), `tests` (complétés),
  `ai_pending`.
- états : **vide** (aucun test) / **pending** (poll + écran « L'oracle relit tes
  épreuves… » réutilisant l'animation décrite §Onboarding du design brief) / **ready**.
- contenu *ready* : synthèse transversale (typewriter possible) + grille des 15 Voies
  en *stagger* ; chaque carte montre `appui_tests` (badges des tests qui soutiennent la
  piste) → rend tangible le « croisement ». Bouton « Régénérer » → `grimoire.refresh`.
- réutiliser les composants de carte métier déjà présents dans `ResultsShow.vue`
  (factoriser en `components/VoieCard.vue` si besoin).

**Navigation** : ajouter l'entrée « Le Grimoire » (→ `grimoire.show`) dans le layout
candidat, à côté de `/history`. Badge « nouveau » si `generated_at` plus récent que la
dernière visite (option neuromarketing — effet Zeigarnik déjà dans la philosophie projet).

---

## 8. Cas limites & garde-fous

| Cas | Comportement attendu |
|---|---|
| 0 test complété | Pas de génération ; page Grimoire « vide » avec CTA vers `/tests`. |
| 1 seul test | Grimoire généré mais signalé « basé sur 1 épreuve » ; la valeur croisée arrive dès le 2e. (On ne *bloque* pas, pour ne pas frustrer.) |
| Échec IA | `status=failed` + texte de repli ; page s'affiche (jamais de spinner infini). Bouton Régénérer dispo. |
| Re-complétion d'un test | Signature change → régénération ; sinon skip. |
| Suppression RGPD | `ProfileGrimoire` supprimé en cascade (`user_id` FK cascade) ; vérifier `GdprController` (export + delete) inclut la nouvelle table. |
| Partage public `/p/{token}` | Décision à prendre : exposer le Grimoire plutôt que la « dernière tentative » ? (recommandé, plus représentatif — petit ajustement dans `ProfileShareController::show`). |
| Coût tokens | 1 appel / test fini. Avec ~5-10 tests, négligeable ; la signature évite les re-générations à vide. |

---

## 9. Tests (à ajouter)

- `tests/Feature/GrimoireFlowTest.php` : compléter 2 tests → assert qu'une ligne
  `profile_grimoires` `ready` existe, contient N voies, `tests_included` = 2.
- signature : re-dispatch sans nouveau test → pas de second appel IA (mock driver,
  assert 0 appel).
- fallback : driver qui throw → `status=failed`, page accessible.
- accès : user A ne voit pas le grimoire de user B (déjà le pattern `abort_unless`).
- `PromptBuilder::globalGrimoire()` : unit test sur la structure du message (multi-tests).

---

## 10. Déploiement (workflow OVH du projet)

1. Windows → commit → push GitHub.
2. `deploy-ovh.ps1` / `deploy-server.sh` (cf. mémoire déploiement).
3. **`php artisan migrate`** sur le serveur (nouvelle table).
4. Build front sur le serveur (`public/build` gitignoré — cf. mémoire déploiement).
5. Vérifier `ANTHROPIC_API_KEY` présent (déjà utilisé par les insights par test).
6. Smoke test : compléter un 2e test → `/grimoire` se peuple.

---

## 11. Checklist fichiers

**Créer**
- `database/migrations/XXXX_create_profile_grimoires_table.php`
- `app/Models/ProfileGrimoire.php`
- `app/Core/AI/Services/GlobalGrimoireService.php`
- `app/Core/AI/Concerns/ParsesAiJson.php` (trait factorisé)
- `app/Jobs/GenerateGlobalGrimoire.php`
- `app/Http/Controllers/Candidate/GrimoireController.php`
- `resources/js/Pages/Candidate/Grimoire.vue`
- (option) `resources/views/pdf/grimoire.blade.php`
- `tests/Feature/GrimoireFlowTest.php`

**Modifier**
- `app/Core/AI/PromptBuilder.php` (+ `globalGrimoire()`)
- `config/ai.php` (+ tâche `global_grimoire`)
- `app/Jobs/GenerateAttemptInsights.php` (+ dispatch du grimoire)
- `app/Models/User.php` (+ `hasOne(ProfileGrimoire)` + helper)
- `routes/web.php` (+ routes grimoire)
- layout candidat (lien nav)
- `app/Http/Controllers/GdprController.php` (export/delete inclut la table)
- (option) `ProfileShareController.php` (partager le grimoire)
- (refacto) `JobSuggestionService` / `ResultController` si on extrait les traits partagés

---

## 12. Ordre d'implémentation suggéré

1. Migration + modèle + relation User.
2. `PromptBuilder::globalGrimoire()` + config tâche + trait JSON.
3. `GlobalGrimoireService` (testable en isolation avec driver mocké).
4. Job + accroche dans `GenerateAttemptInsights` + signature.
5. Controller + routes.
6. Page Vue + nav.
7. RGPD + partage + PDF (finitions).
8. Tests + déploiement.
