# Roadmap de refactor architectural — PraxiQuest

> Rédigé le 2026-06-22. Issu du reliquat de l'audit multi-agents du 2026-06-21
> (`RAPPORT-AUDIT-MULTI-AGENTS-2026-06-21.md`). Objectif : réduire la dette sans
> régression, par lots indépendants et déployables séparément.

Trois chantiers : (A) mutualisation des 5 plugins parcours, (B) unification des
systèmes de tokens design, (C) bibliothèque de composants Vue. Aucun n'est bloquant
pour la production ; ils paient surtout la vélocité future.

---

## A. Mutualiser les 5 plugins « parcours »

### Constat
Cinq plugins répliquent à l'identique la mécanique de parcours quotidien :

- `praxizen`, `praxispeak`, `praxiself`, `praxilink`, `praxiflow`
- chacun a **sa propre** migration `…_create_journey_progress_table.php` et **son propre**
  modèle `src/Models/JourneyProgress.php` (5 copies quasi identiques).
- conséquence déjà rencontrée : garde `Schema::hasTable()` ajoutée sur les 5 migrations
  dupliquées pour éviter les collisions (cf. audit du 21/06).

### ⚠️ Correction (constat réel, 2026-06-22)
La table est **déjà unifiée** : `App\Models\JourneyProgress` (`app/Models/JourneyProgress.php`)
utilise **une seule** table `journey_progress` avec colonne `plugin_slug` et expose déjà toute
la logique en statique (`currentDay()`, `streakFor()`, `completedDays()`, `completionRate()`,
`markComplete()`). Les 5 `plugins/*/src/Models/JourneyProgress.php` + leurs migrations sont des
**doublons résiduels** (la garde `Schema::hasTable()` fait qu'une seule migration crée réellement
la table). **Ce n'est donc PAS une migration de données** — c'est un **nettoyage** : zéro risque
sur les données existantes (même table physique).

### Cible
Les 5 plugins consomment le modèle cœur `App\Models\JourneyProgress` ; on supprime les copies.

- Supprimer les 5 `plugins/{praxizen,praxispeak,praxiself,praxilink,praxiflow}/src/Models/JourneyProgress.php`.
- Supprimer les 5 migrations `…_create_journey_progress_table.php` dupliquées (garder une seule
  source — idéalement déplacer la création de table côté cœur `database/migrations/` si pas déjà le cas).
- Repointer chaque contrôleur/provider plugin qui référence le modèle local vers `App\Models\JourneyProgress`.
- (Optionnel) extraire la config parcours par plugin (durée, phases) dans un contrat `HasJourney`.

### Risque & garde-fou
**Faible** : même table physique, aucune reprise de données. Le seul risque est un `use`/namespace
oublié dans un contrôleur plugin → `php -l` + `npm run build` + un passage de parcours en test
suffisent. Vérifier d'abord quels fichiers plugin référencent le modèle local
(`grep -r "JourneyProgress" plugins/`) **avant** de supprimer.

### Lots
- A1 : repointer les 5 plugins vers `App\Models\JourneyProgress` (additif, sans rien supprimer).
- A2 : supprimer les 5 modèles + 5 migrations dupliqués une fois A1 validé en prod.

---

## B. Unifier les systèmes de tokens design

### Constat — 3 systèmes coexistent (emplacements réels)
1. **`--pt-*`** (parchemin/premium) : défini **inline** dans `resources/views/app.blade.php`
   (`:root`), utilisé par `CandidateLayout`, `ScoreGauge`, `SynthesisCard`, la majorité des pages.
2. **Scheme « base »** `--color-* / --text-* / --bg-* / --glass-*` : défini dans
   `resources/css/app.css` (`:root`), utilisé par les styles globaux et par
   `resources/js/Pages/Candidate/ResultsShow.vue` (classes `.ac-*` + `var(--text-primary)`,
   `var(--glass-border)`). NB : `.ac-*` sont des **noms de classes**, pas des tokens.
3. **Tailwind brut** : couleurs hardcodées (`text-slate-600`, `bg-emerald-50`,
   `text-indigo-700`…) dans plusieurs `*Result.vue` (praximet, praxicare), en contradiction
   avec la règle « n'utiliser que pt-* ».

### Cible
`--pt-*` comme **source unique**. Le scheme « base » est aliasé sur `--pt-*` puis résorbé.

**✅ B1 fait (2026-06-22)** dans `app.css` : `--color-primary` → `var(--pt-gold)` et
`--text-primary` → `var(--pt-text)` (valeurs **byte-identiques**, zéro régression, réversible).
⚠️ `--glass-border` (rgba α0.25) et `--text-muted` (#8C7A5E) **n'ont pas** d'équivalent `--pt-*`
exact (pt : border α0.15, gold-border α0.4 ; text-muted pt #8B7355) → décision design requise
en B2/B3, ne pas aliaser à l'aveugle.

- Étape 1 : dans `app.css`, aliaser temporairement les tokens AC sur les pt-*
  (`--text-primary: var(--pt-text)`, `--glass-border: var(--pt-border)`, …) → zéro régression visuelle immédiate.
- Étape 2 : remplacer dans `ResultsShow.vue` les classes `ac-*`/tokens AC par les pt-*,
  puis retirer les alias.
- Étape 3 : passe de remplacement des couleurs Tailwind brutes par les tokens pt-*
  (cibler praximet, praxicare en priorité ; grep `text-slate|bg-emerald|text-indigo|#[0-9a-fA-F]{6}` dans `plugins/**/*.vue`).

### Risque & garde-fou
Risque faible mais **visuel** : valider par captures avant/après sur 3 pages témoins
(une pt-*, ResultsShow, une Tailwind-brute). Étape 1 (alias) est réversible et sans effet visible.

### Lots
- B1 : alias AC→pt dans `app.css` (filet de sécurité, non visible).
- B2 : purge des tokens AC dans ResultsShow.
- B3 : purge des couleurs Tailwind brutes plugin par plugin.

---

## C. Bibliothèque de composants Vue

### Constat
Beaucoup de markup dupliqué entre pages résultats : carte synthèse, carte métier suggéré,
badge de score, jauge, carte exercice, grille de parcours 60 jours, disclaimer.

### Déjà fait (2026-06-22)
- `SynthesisCard.vue` — créé et propagé à **toutes** les pages résultats (8 plugins +
  `_template`) + `ResultsShow` + `SharedView`. Supprime les `white-space:pre-line` dupliqués.
- `Disclaimer.vue` — encart d'avertissement à liseré or (slot HTML). **Propagé à praxifocus**
  (preuve) ; reste à propager à praxisens, praxizen.
- `JobCard.vue` — carte « métier suggéré » (clés FR/EN tolérées). **Créé**, à propager aux
  boucles `suggested_jobs` de praximet, praxisens, `_template`, praxis360.
- `PathCard.vue` + `PathTier.vue` — cartes/paliers des pistes PTP (cf.
  `PLAN-PISTES-DYNAMIQUES-PTP.md`). **Créés**, à câbler quand le contrôleur exposera les matches.
- Existants antérieurs : `ScoreGauge.vue`, `MarkdownText.vue`, `RadarChart.vue`,
  `ShareProfileButton.vue`.

### Cible — composants à extraire (par fréquence de duplication)
| Composant | Remplace | Pages concernées |
|---|---|---|
| `JobCard.vue` | carte « métier suggéré » (titre, fit %, secteur, pourquoi, next step) | praximet, praxisens, _template, 360… |
| `Disclaimer.vue` | encart « ceci n'est pas un diagnostic » | praxifocus, praxisens, praxizen |
| `ScoreHeader.vue` | en-tête score global + label + jauge | quasi toutes |
| `JourneyGrid.vue` | grille des 60 jours + phases | les 5 plugins parcours (cf. chantier A) |
| `ExerciseCard.vue` | carte exercice (accordéon, difficulté, catégorie) | praxizen, praxiself, praxiboost |
| `PathCard.vue` / `PathTier.vue` | cartes pistes PTP (cf. `PLAN-PISTES-DYNAMIQUES-PTP.md`) | restitution PTP |

### Convention
Tous sous `resources/js/Components/`, importés via l'alias `@/Components/…` (déjà supporté
dans les plugins, confirmé par `ScoreGauge`/`SynthesisCard`). Props typées, `v-if` géré par
l'appelant quand un `v-else` voisin existe, style scoped en tokens `--pt-*` uniquement.

### Risque & garde-fou
Faible. Chaque extraction est isolée : créer le composant, migrer 1 page, vérifier, puis
propager. Toujours porter le `v-if` côté appelant si un `v-else` adjacent existe (piège
rencontré lors de l'extraction de `SynthesisCard`).

### Lots
- C1 (fait) : `SynthesisCard`.
- C2 : `JobCard` + `Disclaimer` (gros gain, faible risque).
- C3 : `ScoreHeader`.
- C4 : `JourneyGrid` + `ExerciseCard` (à coupler avec le chantier A).

---

## Séquencement conseillé

1. **B1** (alias tokens) — filet de sécurité, 30 min, zéro risque.
2. **C2** (`JobCard`/`Disclaimer`) — gain visible, faible risque.
3. **A1** (`JourneyService` + table commune, double-écriture) — prépare la mutualisation sans rien casser.
4. **B2/B3**, **C3** — nettoyage progressif.
5. **A2** (bascule + reprise data) — dernier, car destructif.

> Validation : chaque lot passe `php -l` + `npm run build` **côté Windows** (le sandbox
> Cowork n'a pas PHP/Node — cf. `praxiquest_deploy`), captures avant/après pour les lots
> visuels, et `php artisan migrate --pretend` pour les lots A.
