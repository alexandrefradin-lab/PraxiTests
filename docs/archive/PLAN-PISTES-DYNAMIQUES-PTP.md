# Plan d'implémentation — Pistes dynamiques PTP

> Statut : conception prête à coder. Rédigé le 2026-06-22.
> Référence concept : note mémoire `praxiquest-dynamic-pistes-ptp`.

## 1. Principe directeur

Le **score d'un test ne bouge jamais** : c'est la mesure, elle reste figée. Ce qui
devient dynamique, ce sont les **pistes métiers** ouvertes à la personne. Une piste
s'ouvre quand l'écart entre le profil et le métier cible se comble avec une formation
**finançable et réaliste**.

Filtre central = **PTP** (Projet de Transition Professionnelle) : une piste entre dans
la liste si l'écart se comble avec **≤ 1 an de formation**. L'outil devient un moteur de
transitions finançables, pas une liste d'idées.

Une bonne piste coche **3 critères** :
1. **Fit** — cohérence avec les résultats des tests (appétence + aptitude).
2. **Finançable** — écart de formation ≤ 1 an (cœur PTP).
3. **Marché** — ça recrute (volume + tension + tendance).

## 2. Données d'entrée (déjà collectées)

Tout est déjà dans le modèle `App\Models\Profile` (`app/Models/Profile.php`) :

| Champ | Usage PTP |
|---|---|
| `status` | salarié / entrepreneur / demandeur d'emploi → **éligibilité PTP** (salariés d'abord) |
| `status_since` / `status_months` | ancienneté → condition d'ouverture des droits PTP |
| `current_role` | point de départ de la transition (écart métier source→cible) |
| `industry` | secteur actuel, sert au calcul de proximité |
| `cv_structured` (array) | acquis déclarés (diplômes, expériences) → réduit l'écart de formation |
| `cv_extracted_text` | fallback texte brut pour l'IA |

Côté tests : `scoring` (dimensions normalisées 0–100) de chaque `TestResult`, agrégés au
niveau du candidat (déjà fait pour le Grimoire global — voir `GlobalGrimoireService`).

## 3. Public cible & garde-fous

- **Salariés d'abord** : le PTP ne concerne que les salariés. Pour `status = demandeur d'emploi`
  ou `entrepreneur`, afficher les pistes mais signaler que le dispositif de financement
  diffère (CPF / AIF / autres), à traiter dans un lot ultérieur.
- Donnée marché **datée + régionale**, toujours affichée comme *indicative* (date + source).
  On informe, on ne garantit pas un emploi.
- Donnée « durée de formation » de niveau 1 = **estimation par famille de métiers**.
  La logique de calcul ne change pas quand la donnée gagne en rigueur (voir §7).

## 4. Modèle de données

Deux tables nouvelles + extension du résultat existant.

### 4.1 `career_paths` — référentiel des 30+ pistes (catalogue, partagé)

```
id                bigint PK
slug              string unique            // ex. "developpeur-web"
title             string                   // "Développeur web"
family            string                   // famille de métiers (pour estim. niveau 1)
rome_code         string nullable          // mapping France Travail (offres, tension, BMO)
rncp_codes        json nullable            // certifications cibles (formation)
formation_months  unsigned smallint        // durée estimée pour combler l'écart "standard"
market_demand     enum('faible','moyen','fort')  // niveau 1 : saisi à la main
market_trend      enum('declin','stable','croissance')
salary_indicative json nullable            // {min, max, median, currency}
fit_dimensions    json                     // dimensions de scoring qui nourrissent le fit
active            boolean default true
timestamps
```

### 4.2 `profile_path_matches` — résultat calculé par candidat (cache)

```
id                  bigint PK
profile_id          FK profiles
career_path_id      FK career_paths
fit_score           unsigned tinyint        // 0–100, dérivé des tests (FIXE une fois calculé)
formation_gap_months unsigned smallint      // écart APRÈS prise en compte des acquis/CV
tier                enum('accessible','ptp','horizon')  // palier de restitution
opportunity_index   unsigned tinyint        // 0–100 = f(fit, finançable, marché)
unlocked            boolean default false    // déblocage déclaratif/module
computed_at         timestamp
timestamps
unique(profile_id, career_path_id)
```

> `fit_score` est figé à la première mesure (intégrité du score). Seuls
> `formation_gap_months`, `tier` et `unlocked` évoluent quand la personne ajoute des
> formations/acquis (déblocage).

## 5. Algorithme des 3 paliers

Pour chaque `career_path` candidate :

```
acquis            = lecture cv_structured + formations déclarées + statut/ancienneté
gap_brut          = formation_months(path)                  // durée standard de la cible
formation_gap     = max(0, gap_brut − crédit_acquis(acquis, path))

tier =
  formation_gap == 0          → "accessible"   (0 formation)
  formation_gap ≤ 12 (mois)   → "ptp"          (≤ 1 an, finançable — cœur de l'offre)
  formation_gap  > 12         → "horizon"      (ambition hors PTP)

opportunity_index = round(
      0.45 * fit_score
    + 0.30 * financabilite(formation_gap)      // 100 si accessible, dégressif jusqu'à 12 mois, 0 au-delà
    + 0.25 * marche(market_demand, market_trend, tension_rome)
)
```

- On **classe les 30 pistes** par `opportunity_index` décroissant.
- On garde les 30 meilleures **dont au moins X en palier `ptp`** (cible produit : la
  majorité doit être finançable, sinon l'outil perd son sens).
- `financabilite()` et `marche()` sont des fonctions pures et testables (cibles de tests unitaires).

## 6. Intégration IA & code

L'actuel `Praxis\Core\AI\Services\JobSuggestionService` (`app/Core/AI/Services/JobSuggestionService.php`)
produit 15 métiers en texte libre. On l'étend, sans casser l'existant :

1. **Config** : passer `praxiquest.results.suggested_jobs_count` 15 → **30** (déjà lu en ligne 19).
2. **Prompt** : `PromptBuilder::jobSuggestions()` doit demander, pour chaque métier, un
   objet structuré incluant `famille`, `rome_hint`, `formation_estimee_mois`. L'IA propose,
   le back **recale** sur le catalogue `career_paths` (source de vérité chiffrée).
3. **Nouveau service** `Praxis\Core\AI\Services\PtpPathService` :
   - prend l'IA comme générateur de *candidats* + le catalogue comme *référentiel*,
   - applique l'algorithme §5, écrit `profile_path_matches`,
   - réutilise le hook plugin existant `PluginHooks::applyFilters('jobs.suggested', …)`.
4. **Déblocage** : un recalcul (`PtpPathService::recompute(Profile)`) est déclenché quand la
   personne ajoute une formation → recalcule `formation_gap` + `tier` + `unlocked`. Même
   logique de paliers que les Éclats (note `praxiquest-eclats-praxiboost`).

### Mapping métier → ROME / RNCP

- Clé technique : `rome_code` débloque les données **France Travail** (offres, tension, BMO,
  salaires) via leur API gratuite. `rncp_codes` débloque le **catalogue formation** + éligibilité PTP.
- Même table de pistes, deux mappings parallèles. Démarrage : codes saisis à la main sur les
  30 premières pistes ; montée en charge : import API.

## 7. Niveaux de rigueur (montée progressive, même algo)

| Niveau | Durée formation | Marché | Effort |
|---|---|---|---|
| **1 (MVP)** | estimation par famille (`formation_months` saisi) | `market_demand`/`market_trend` saisis à la main | catalogue de 30 pistes en seed |
| **2** | certifications RNCP réelles | import France Travail via ROME (tension, offres) | 2 intégrations API |
| **3** | catalogue formations réel + éligibilité PTP vérifiée | temps réel + géo (bassin d'emploi) | enrichissement continu |

La logique de calcul (§5) est identique à tous les niveaux ; seule la **qualité de la donnée**
d'entrée s'améliore. On peut donc livrer le niveau 1 tout de suite.

## 8. UX de la restitution

Trois sections empilées, classées par `opportunity_index` :

1. **Accessible maintenant** — `tier = accessible`. Badge vert « 0 formation ».
2. **À portée via PTP** — `tier = ptp`. Bloc central, mis en avant : « finançable, ≤ 1 an ».
   Affiche le **chemin de déblocage** (quelle formation comble l'écart).
3. **Horizon long** — `tier = horizon`. Replié par défaut, ambition.

Chaque carte de piste affiche : titre, `fit_score`, **bloc marché** (demande, tendance,
salaire indicatif, date + source), et l'action de déblocage (« j'ai/je vise cette formation »).

Réutiliser `SynthesisCard` / `MarkdownText` pour les textes générés, et le style parchemin
(`--pt-*`). Nouveau composant Vue suggéré : `@/Components/PathCard.vue` (carte piste) +
`@/Components/PathTier.vue` (section palier).

## 9. Découpage en lots livrables

- **Lot 0 (fait)** : décision concept + ce plan.
- **Lot 1 — MVP niveau 1 — backend + composants FAITS le 2026-06-22** :
  migrations `career_paths` + `profile_path_matches`, modèles `CareerPath`/`ProfilePathMatch`,
  `PtpPathService` (algo §5, fonctions pures testées dans `tests/Unit/PtpPathServiceTest.php`),
  `CareerPathsSeeder` (30 pistes), config `results.career_paths_count = 30`, composants
  `PathCard.vue` + `PathTier.vue`. **Aucune API externe.**
  **Reste pour finir le Lot 1** : (a) câblage contrôleur → exposer les matches via Inertia à la
  page de restitution (Grimoire global ou résultats), (b) action de déblocage déclaratif qui écrit
  `profiles.metadata->formation_credit_months` puis appelle `PtpPathService::recompute()`,
  (c) valider `php -l` + `php artisan migrate` + `pest tests/Unit/PtpPathServiceTest.php` côté Windows.
- **Lot 2** : mapping ROME + import France Travail (marché temps réel) ; mapping RNCP.
- **Lot 3** : déblocage par module interne (trace réelle) + géo bassin d'emploi + dispositifs
  hors-PTP (demandeur d'emploi / entrepreneur).

## 10. Fichiers à créer / modifier (Lot 1)

À créer :
- `database/migrations/xxxx_create_career_paths_table.php`
- `database/migrations/xxxx_create_profile_path_matches_table.php`
- `database/seeders/CareerPathsSeeder.php` (30 pistes, niveau 1)
- `app/Models/CareerPath.php`, `app/Models/ProfilePathMatch.php`
- `app/Core/AI/Services/PtpPathService.php`
- `tests/Unit/PtpPathServiceTest.php` (financabilité, marché, classement, paliers)
- `resources/js/Components/PathCard.vue`, `resources/js/Components/PathTier.vue`

À modifier :
- `config/praxiquest.php` → `results.suggested_jobs_count = 30`
- `app/Core/AI/PromptBuilder.php` → sortie structurée (famille, rome_hint, formation_estimee_mois)
- page(s) de restitution (Grimoire global / résultats) → intégrer les 3 paliers

> Note environnement : valider toute migration/seed avec `php artisan migrate --pretend`
> puis `php -l` **côté Windows** avant déploiement (le sandbox Cowork n'a pas PHP — cf.
> note mémoire `praxiquest_deploy`). Le code des services est volontairement laissé en
> spec ici pour ne pas injecter de PHP non validé dans le chemin d'autoload/migrations.
