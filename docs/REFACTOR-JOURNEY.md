# REFACTOR-JOURNEY — Mutualisation des parcours quotidiens

> Suite du finding **T1** de l'audit multi-agents du 21/06 (duplication des
> mini-apps « 60 jours »). Pilote migré : **praxivision** (2026-07-16).
> Ce guide décrit la migration des plugins restants, un par un, **sans aucun
> changement de comportement observable** (mêmes routes, mêmes props Inertia,
> même rendu, aucune migration de base de données).

---

## 1. Cartographie de la duplication

Deux familles de plugins « parcours quotidien » coexistent :

### Famille A — parcours « registre » (contenu en dur, table partagée)

Déjà largement mutualisée côté PHP : `JourneyRegistry` + `JourneyEngine` +
`WeeklyPhaseAdapter` (app/Core/Journey), `JourneyDashboardController`,
pages génériques `Candidate/JourneyIndex.vue` / `Candidate/JourneyPractice.vue`,
modèle partagé `App\Models\JourneyProgress` (tables `journey_starts` /
`journey_progress`).

| Plugin | Enregistrement registre | Duplication restante |
|---|---|---|
| praxiflow | `PluginServiceProvider` → `JourneyRegistry::register('praxiflow', …)` | `PraxiFlowResult.vue` (onglet Parcours : phaseColor/phaseLabel/dayStatus), `src/Models/JourneyProgress.php` **mort** |
| praxilink | idem | `PraxiLinkResult.vue` (variante), `src/Models/JourneyProgress.php` **mort** |
| praxiself | idem | `PraxiSelfResult.vue` (variante sans grille), `src/Models/JourneyProgress.php` **mort** |
| praxispeak | idem | `PraxiSpeakResult.vue` (cellColor/currentPhase/streak), `src/Models/JourneyProgress.php` **mort** |
| praxizen | idem | `PraxiZenResult.vue` (PHASES/cellColor/currentPhase/phaseProgress), `src/Models/JourneyProgress.php` **mort** |

Les modèles `plugins/*/src/Models/JourneyProgress.php` de ces cinq plugins ne
sont **référencés nulle part** (seul `App\Models\JourneyProgress` est utilisé) :
ce sont des reliquats supprimables.

### Famille B — parcours « tables dédiées » (contenu seedé en BDD)

C'est ici que vit la duplication PHP massive. Similarité mesurée après
normalisation des noms (préfixe de tables, classes, slug) :

| Plugin | Service | Contrôleur | Modèles (×3) | Routes | Vue Index | Vue Jour |
|---|---|---|---|---|---|---|
| **praxilead** (référence) | `JourneyService` | `PracticeController` | `MgmtJourney` / `MgmtPractice` / `MgmtPracticeProgress` | `/management` | `PraxiLeadIndex` | `PraxiLeadPractice` |
| **praxivision** *(pilote migré)* | 82 % | 97 % | 100 % | 92 % | script 100 % identique | script partiel |
| **praxizenith** | 91 % | 89 % | 100 % | 62 %* | script 100 % identique (props `exercises`) | script partiel |
| **praximiroir** | 76 %** | 82 % | ~100 % | 62 %* | script identique modulo `bloc` + icônes | script partiel |

\* Les routes ne diffèrent que par le préfixe d'URL et les noms de contrôleur.
\** praximiroir : 30 jours au lieu de 60, champs `bloc`/`reflection`/`prompt`.

**Cas à part — praxiboost** : déblocage **par paliers d'Éclats cumulés**
(`ExerciseUnlocker`), pas de cadence quotidienne ni de `started_on`. Il ne
relève PAS de cette abstraction : ne pas le migrer vers `DailyJourneyService`.

---

## 2. Le noyau générique (créé avec le pilote)

| Fichier | Rôle |
|---|---|
| `app/Core/Journey/DailyJourneyService.php` | Cadence mutualisée (journeyFor, currentDay, isUnlocked, daysUntilUnlock, streakFor). Abstrait : chaque plugin fournit `journeyModel()`, `progressModel()`, `progressTable()`, `totalDays()`. |
| `app/Core/Journey/Http/DailyPracticeController.php` | Contrôleur mutualisé index/show/complete (gating trésor, mapping des jours, complétion + Éclats une seule fois). Points de variation surchargeables : clés de props, libellés, payloads, règles de validation. |
| `resources/js/composables/useJourney.js` | `useJourney(props, options)` : logique des pages Index (iconFor, todayItem, donePercent, dayStrip, upcomingDays, currentBlock, blocks). `journeyIconFor` : résolution d'icône seule. `useJourneyGrid(options)` : grille de restitution des pages Result (phases, cellColor, dayStatus) paramétrée par les bornes/couleurs HISTORIQUES de chaque plugin. |

Le pilote **praxivision** montre le résultat attendu :

- `plugins/praxivision/src/Services/LeadershipJourneyService.php` → ~40 lignes
  de configuration (constantes conservées : `TOTAL_DAYS`, `ECLATS_PER_PRACTICE`).
- `plugins/praxivision/src/Http/PracticeController.php` → ~60 lignes de
  configuration (slug, modèles, pages).
- `PraxiVisionIndex.vue` → script réduit au destructuring de `useJourney`
  (`todayItem` renommé `todayPractice`), template inchangé.
- `PraxiVisionPractice.vue` → `iconFor` importé, reste inchangé (la logique de
  sections `exerciseSections`/`renderLines` lui est propre).

---

## 3. Guide de migration par plugin

Règle d'or : **comparer chaque libellé et chaque clé de prop avec le fichier
d'origine avant de supprimer quoi que ce soit.** Les défauts du contrôleur
mutualisé reproduisent praxivision ; les autres plugins DOIVENT surcharger ce
qui diffère. Vérifier après chaque plugin : `php -l` sur les fichiers touchés,
`npm run build`, puis un passage manuel sur `/…/`, `/…/jour/1` (ou `/exercice/1`),
et une complétion.

### 3.1 praxilead (« Le Cap » — /management)

1. `src/Services/JourneyService.php` → sous-classe de `DailyJourneyService` :
   - garder `public const TOTAL_DAYS = 60;` et `public const ECLATS_PER_PRACTICE = 15;`
     (⚠ 15, pas 20 comme praxivision) ;
   - `journeyModel()` → `MgmtJourney::class`, `progressModel()` →
     `MgmtPracticeProgress::class`, `progressTable()` → `'mgmt_practice_progress'`,
     `totalDays()` → `self::TOTAL_DAYS`.
2. `src/Http/PracticeController.php` → sous-classe de `DailyPracticeController` :
   - constructeur : injecter `JourneyService` et le passer au parent ;
   - `slug()` `'praxilead'`, `itemModel()` `MgmtPractice::class`,
     `progressModel()` `MgmtPracticeProgress::class`,
     `indexPage()` `'PraxiLeadIndex'`, `showPage()` `'PraxiLeadPractice'`,
     `eclatsPerItem()` `JourneyService::ECLATS_PER_PRACTICE` ;
   - **PIÈGE** : surcharger `completedMessage()` → `'Pratique appliquée ! +…'`
     (praxilead dit « appliquée », pas « intégrée »).
3. `resources/js/Pages/PraxiLeadIndex.vue` : script identique à l'ancien
   PraxiVisionIndex → remplacer par `useJourney(props, { itemsKey: 'practices', groupKey: 'theme' })`
   (destructurer `todayItem: todayPractice`). Garder `appName` local.
4. `PraxiLeadPractice.vue` : remplacer le `iconFor` local par
   `import { journeyIconFor as iconFor } from '@/composables/useJourney'`.
   Le reste (form, submit, confetti `particleCount: 90`) est propre au plugin.
5. Supprimer le corps dupliqué de l'ancien service/contrôleur (les fichiers
   restent, réduits à la configuration).

### 3.2 praxizenith (« Le Sanctuaire » — /sanctuaire, vocabulaire « exercice »)

1. `src/Services/FocusJourneyService.php` → sous-classe :
   - garder `TOTAL_DAYS = 60` et **`ECLATS_PER_EXERCISE = 15`** (nom de
     constante différent — le conserver, il est utilisé par le contrôleur) ;
   - `journeyModel()` `FocusJourney::class`, `progressModel()`
     `FocusExerciseProgress::class`, `progressTable()` `'focus_exercise_progress'`.
2. `src/Http/FocusController.php` → sous-classe :
   - `slug()` `'praxizenith'`, `itemModel()` `FocusExercise::class`,
     `indexPage()` `'PraxiZenithIndex'`, `showPage()` `'PraxiZenithExercise'`,
     `eclatsPerItem()` `FocusJourneyService::ECLATS_PER_EXERCISE` ;
   - **PIÈGES — tout le vocabulaire diffère** :
     - `itemsProp()` → `'exercises'` ; `itemProp()` → `'exercise'` ;
     - `eclatsProp()` → `'eclatsPerExercise'` ;
     - `xpReason()` → `'praxizenith.exercise_done'` (pas `.practice_done`) ;
     - `lockedMessage()` → `'Cet exercice se débloquera dans X jour(s).'` ;
     - `completedMessage()` → `'Exercice appliqué ! +…'`.
3. `PraxiZenithIndex.vue` : `useJourney(props, { itemsKey: 'exercises', groupKey: 'theme' })`,
   destructurer `todayItem: todayExercise`.
4. `PraxiZenithExercise.vue` : `journeyIconFor` si le composant a le même
   `iconFor` standard (à vérifier), le reste est propre.

### 3.3 praximiroir (« La Forge de l'Identité » — 30 jours, le plus divergent)

1. `src/Services/MirrorJourneyService.php` → sous-classe :
   - garder **`TOTAL_DAYS = 30`** et `ECLATS_PER_EXERCISE = 20` ;
   - `journeyModel()` `MirrorJourney::class`, `progressModel()`
     `MirrorProgress::class`, `progressTable()` `'mirror_progress'`.
2. `src/Http/MirrorController.php` → sous-classe. **PIÈGES nombreux** :
   - `itemsProp()` `'exercises'`, `itemProp()` `'exercise'`,
     `eclatsProp()` `'eclatsPerExercise'`, `xpReason()` `'praximiroir.exercise_done'` ;
   - `itemSummary()` : la clé de regroupement est **`bloc`** (pas `theme`) →
     surcharger pour émettre `'bloc' => $item->bloc` ;
   - `itemPayload()` : émet `day, bloc, title, summary, body, prompt,
     duration_min, icon` — **pas** de `theme` ni `micro_challenge`, mais un
     champ **`prompt`** ;
   - `statePayload()` : `completed, reflection, felt_score` (**pas** de `notes`,
     ordre différent) ;
   - `validationRules()` : `reflection` (string max **5000**) + `felt_score` —
     **pas** de `notes` ;
   - `applyProgressData()` : reporter `reflection` puis `felt_score` ;
   - `completedMessage()` `'Exercice accompli ! +…'`,
     `updatedMessage()` `'Réflexion mise à jour.'`.
3. `PraxiMiroirIndex.vue` :
   `useJourney(props, { itemsKey: 'exercises', groupKey: 'bloc', icons: MIROIR_ICONS })`
   avec sa carte d'icônes locale (camera, mountain, fingerprint… — elle est
   propre au plugin, la passer en option, ne pas l'ajouter à `JOURNEY_ICONS`).
   **PIÈGE** : le computed s'appelle `blocs` (pas `blocks`) dans le template →
   destructurer `blocks: blocs`, et chaque groupe expose `block.bloc`.
4. `PraxiMiroirExercise.vue` : icônes locales également — garder le `iconFor`
   local ou passer la carte à `journeyIconFor(name, MIROIR_ICONS)`.

### 3.4 Famille A — pages Result (front uniquement)

Migrer une page à la fois vers `useJourneyGrid`, en conservant les bornes et
couleurs HISTORIQUES de chaque plugin (elles diffèrent réellement — ne pas
uniformiser sans décision produit) :

- **PraxiZenResult.vue** : phases 1–15 / 16–30 / 31–45 / 46–60, couleurs
  `#7C3AED / #0284C7 / #059669 / #EA580C`, `todayColor: 'var(--pt-gold)'`.
  Remplace `PHASES`, `safeCurrentDay`, `currentPhaseKey`, `currentPhase`,
  `phaseProgress`, `journeyDone`, `cellColor`, `phaseLabel`.
- **PraxiFlowResult.vue** : phases 1–14 / 15–28 / 29–42 / 43–60, couleurs
  CSS-var (`var(--pt-indigo)`, `var(--pt-info)`, `var(--pt-warning)`,
  `var(--pt-success)`). Remplace `phaseColor`, `phaseLabel`, `dayStatus`.
  **PIÈGE** : le template calcule la phase inline
  (`n <= 14 ? 'decouverte' : …`) → remplacer par `phaseKeyFor(n)`.
- **PraxiSpeakResult.vue / PraxiLinkResult.vue** : mêmes principes, vérifier
  bornes/couleurs propres avant migration (chaque page a sa variante).
- Les props d'entrée diffèrent (flow lit `result.journey`, zen lit des props
  racine `journeyProgress`/`currentDay`) : `useJourneyGrid` prend des
  refs/computed, chaque page construit `currentDay` et `completedDays`
  (Set) à sa façon — ne pas toucher aux contrôleurs.

### 3.5 Nettoyage (après migration de tous les plugins)

- Supprimer les modèles morts `plugins/{praxiflow,praxilink,praxiself,praxispeak,praxizen}/src/Models/JourneyProgress.php`
  (vérifier une dernière fois avec un grep `Plugins\\Praxi…\\Models\\JourneyProgress`).
- Envisager d'aligner les noms de constantes (`ECLATS_PER_PRACTICE` vs
  `ECLATS_PER_EXERCISE`) — changement interne sans effet observable.
- `JourneyController` (app/Http/Controllers/Candidate) et son API
  `/journey/*` : liste de slugs en dur (`in:praxizen,praxiself,…`) + match de
  classes `Data\Journey` — pourrait lire `JourneyRegistry::slugs()` à terme.

---

## 4. Vérifications faites sur le pilote (2026-07-16)

- `php -l` : OK sur les 4 fichiers PHP créés/modifiés (PHP 8.1 local).
- `npm run build` : OK (vite 5, 325 modules, chunk `useJourney` émis).
- Contrat vérifié ligne à ligne contre l'ancien contrôleur praxivision :
  mêmes routes (`praxivision.index/show/complete`), mêmes props
  (`appDescription, practices, currentDay, totalDays, completed, streak` /
  `practice, state, nav, eclatsPerPractice`), mêmes messages flash, même
  raison XP (`praxivision.practice_done`), même octroi unique d'Éclats.
- Aucun test automatisé n'existe pour ces plugins : chaque migration doit être
  vérifiée manuellement (index, jour verrouillé → 403, complétion, streak).
