# Plan — Déblocage d'exercices de développement personnel par paliers d'Éclats

> Statut : **plan à valider** (rien n'est codé). Date : 2026-06-21.
> Décisions validées : Éclat = XP existant · déblocage **par paliers (accumulation, pas de dépense)** · **nouveau module/plugin dédié**.

## 1. Rappel : ce que sont les « Éclats »

Les « Éclats » affichés en haut à droite (`CandidateLayout.vue`, barre XP) sont la valeur `gamification.xp_total`. C'est le **même système que les XP** :

- Gains définis dans `config/gamification.php` : 10/question, 50/section, 200/test terminé, 100/premier test, 50/CV ajouté, 25/insight.
- Moteur : `Praxis\Core\Gamification\GamificationEngine::awardXp()` — incrément atomique, journalise dans `xp_events`, déclenche le hook `gamification.xp_awarded`, recalcule le niveau.
- Stockage : table `gamification_progress`, une ligne par `(user_id, test_id)` (`test_id` nullable).

**Conséquence pour le plan :** l'Éclat n'est jamais « dépensé ». Les paliers se basent sur l'**Éclat total cumulé** de l'utilisateur. On le débloque définitivement une fois le seuil atteint.

## 2. Point d'attention identifié (pré-requis)

Le total global d'Éclats n'est **pas** partagé globalement à Inertia : `HandleInertiaRequests::share()` ne renvoie pas `gamification`. La barre du haut (`page.props.gamification?.xp_total`) n'est donc alimentée que sur les pages qui le passent explicitement (ex. `AttemptController`). De plus, `gamification_progress` est par test → il faut un **total agrégé** (somme des `xp_total` toutes lignes confondues, ou ligne `test_id = null` dédiée).

**Décision proposée :** ajouter un total global cumulé, calculé via `SUM(xp_total)` par utilisateur (mis en cache), et le partager dans `HandleInertiaRequests`. Ça corrige la barre du haut **et** sert de base aux paliers. À valider.

## 3. Architecture cible — nouveau plugin `praxiboost`

On calque la structure du plugin `praxispeak` (déjà un mini-app d'exercices guidés).

```
plugins/praxiboost/
├── plugin.json                       # type "mini-app", namespace Praxis\Plugins\PraxiBoost
├── PluginServiceProvider.php         # extends AbstractPlugin : routes, vues, migrations, seed à l'activation
├── routes/plugin.php                 # /exercices (index, show, complete)
├── database/
│   ├── migrations/
│   │   ├── ..._create_dev_exercises_table.php
│   │   └── ..._create_dev_exercise_progress_table.php
│   └── seeders/DevExercisesSeeder.php
├── src/
│   ├── Data/Exercises.php            # contenu statique (catalogue) → seedé en base
│   ├── Models/DevExercise.php
│   ├── Models/DevExerciseProgress.php
│   └── Services/ExerciseUnlocker.php # logique paliers, branchée sur le hook gamification
└── resources/views/...               # si pages Inertia côté Vue plutôt, voir §6
```

Activation (cf. mémoire) : `php artisan plugins:discover --sync` puis `php artisan plugins:activate praxiboost`.

## 4. Modèle de données

### Table `dev_exercises` (catalogue, administrable)
| colonne | type | rôle |
|---|---|---|
| id | id | |
| slug | string unique | identifiant stable |
| title | string | titre affiché |
| category | string | ex. confiance, gestion du stress, communication, sens/valeurs |
| summary | string | accroche courte (visible même verrouillé) |
| body | longText (markdown) | contenu de l'exercice |
| duration_min | tinyint | durée estimée |
| icon | string nullable | icône |
| threshold_eclats | unsignedInteger | **palier de déblocage** |
| sort_order | smallint | ordre d'affichage |
| is_active | bool | publication |

### Table `dev_exercise_progress` (par utilisateur, calquée sur `journey_progress`)
| colonne | type | rôle |
|---|---|---|
| id | id | |
| user_id | fk users | |
| exercise_slug | string | |
| unlocked_at | timestamp nullable | franchissement du palier |
| completed_at | timestamp nullable | « marqué comme fait » |
| felt_score | tinyint nullable | ressenti 1-5 (optionnel) |
| notes | text nullable | notes perso |
| unique(user_id, exercise_slug) | | |

## 5. Logique de déblocage (paliers)

`ExerciseUnlocker` s'abonne au hook **déjà émis** par le moteur : `PluginHooks::doAction('gamification.xp_awarded', ...)`.

À chaque gain d'Éclat :
1. Recalculer l'Éclat total global de l'utilisateur.
2. Sélectionner les `dev_exercises` actifs dont `threshold_eclats <= total` et qui n'ont pas encore de `unlocked_at`.
3. Créer/mettre à jour leur ligne `dev_exercise_progress` avec `unlocked_at = now()`.
4. Pousser un message flash / toast « Nouvel exercice débloqué : … » (réutilise le système de flash Inertia existant).

Idempotent (ne re-débloque jamais), sans dépense, robuste aux gains rétroactifs.

### Paliers proposés (à ajuster)
| Palier | Éclats requis | Exemple d'exercice | Catégorie |
|---|---|---|---|
| 1 | 200 | Le journal des 3 réussites | Confiance |
| 2 | 500 | Respiration & ancrage avant un enjeu | Gestion du stress |
| 3 | 1000 | Reformuler une croyance limitante | Restructuration cognitive |
| 4 | 2000 | Clarifier ses valeurs cœur | Sens / valeurs |
| 5 | 3500 | Plan d'action 30 jours aligné profil | Passage à l'action |

(Le palier 200 = fin du 1er test ; cohérent avec « le gain d'éclat débloque des exercices ».)

## 6. Interface (Inertia + Vue 3)

- **Nouvelle page** `resources/js/Pages/Candidate/Exercises.vue` : grille de cartes.
  - Carte débloquée : titre, catégorie, durée, bouton « Ouvrir », pastille « fait » si complété.
  - Carte verrouillée : grisée + cadenas + « Débloqué à {threshold} Éclats » + mini-barre de progression vers ce palier.
- **Page détail** `Pages/Candidate/ExerciseShow.vue` : contenu markdown, champ notes, bouton « Marquer comme fait » (+ ressenti 1-5 optionnel).
- **Navigation** : lien « Exercices » dans `CandidateLayout.vue` ; éventuellement un compteur « X/Y débloqués » près de la barre d'Éclats.
- **Cohérence visuelle** : réutiliser les classes `ac-*` existantes (cf. History.vue / AttemptPlay.vue).

## 7. Routes

```
GET  /exercices                → Exercises@index   (liste + état déblocage)
GET  /exercices/{slug}         → Exercises@show    (refus 403 si verrouillé)
POST /exercices/{slug}/done    → Exercises@complete
```
Middleware `['web','auth']` (+ `EnsureSubscribed` si le module doit être réservé aux abonnés — à décider).

## 8. Étapes d'implémentation (ordre)

1. **Pré-requis global** : helper « total Éclats » + partage Inertia dans `HandleInertiaRequests` (corrige aussi la barre du haut).
2. Scaffolder `plugins/praxiboost` (plugin.json + ServiceProvider) sur le modèle praxispeak.
3. Migrations `dev_exercises` + `dev_exercise_progress`.
4. `Data/Exercises.php` (catalogue 5 exercices) + `DevExercisesSeeder`.
5. Modèles Eloquent + `ExerciseUnlocker` branché sur `gamification.xp_awarded`.
6. Controller + routes.
7. Pages Vue (index, show) + lien nav + état verrouillé/déverrouillé.
8. Activation : `plugins:discover --sync` + `plugins:activate praxiboost` + `migrate`.
9. **Vérification** : test manuel (créer un user, lui attribuer des Éclats jusqu'à franchir 200/500, vérifier déblocage + toast + accès refusé sur verrouillé) ; commande `tinker` ou test feature.
10. Déploiement OVH (build sur serveur, cf. mémoire).

## 9. Points à trancher avant de coder

1. **Total Éclats** : somme de toutes les lignes `gamification_progress`, ou bascule vers une ligne globale `test_id = null` ? (Je recommande la somme, non destructif.)
2. **Réservé aux abonnés** (`EnsureSubscribed`) ou ouvert à tous les candidats ?
3. **Admin** : les exercices doivent-ils être éditables dans le back-office, ou figés dans le seeder pour l'instant ?
4. **Paliers/contenu** : valides-tu les 5 paliers proposés et leurs seuils ?
5. **Nom du plugin** : `praxiboost` te convient-il, ou un autre slug (praxigrow, praxistep…) ?

---

## 10. Implémentation réalisée (2026-06-21)

Décisions retenues : module **ouvert** à tous les candidats, contenu **figé** en v1 (seeder), total d'Éclats = **somme** des `xp_total`, paliers **100 / 300 / 700 / 1500 / 3000**, plugin nommé **`praxiboost`**.

**Core**
- `GamificationEngine::totalEclats()` + `globalProgressOf()`.
- `HandleInertiaRequests` partage le total d'Éclats global (corrige la barre du haut partout).
- `CandidateLayout.vue` : lien « Exercices » (affiché seulement si le plugin est actif).

**Plugin `plugins/praxiboost/`**
- `plugin.json`, `PluginServiceProvider` (routes, migrations, hook `gamification.xp_awarded` → déblocage).
- Tables `dev_exercises` + `dev_exercise_progress`.
- Catalogue `Data/Exercises.php` (5 exercices) + `DevExercisesSeeder`.
- `ExerciseUnlocker` (paliers, idempotent), `ExerciseController` (index/show/complete), pages Vue index + détail (rendu markdown, ressenti 1-5, notes, confetti).
- Entrées composer (psr-4 + classmap).

### Activation (sur le serveur OVH, après pull)
```bash
~/composer.phar dump-autoload -o
php artisan plugins:discover --sync
php artisan plugins:activate praxiboost      # lance migrate + seed
php artisan migrate --force                  # filet de sécurité
npm run build                                 # public/build est gitignoré
php artisan optimize:clear
```

> NB : PHP n'est pas disponible dans l'environnement de préparation, donc le `php -l` final et les migrations s'exécutent côté serveur. La syntaxe a été validée structurellement (équilibre accolades/parenthèses, JSON, cohérence routes ↔ pages Inertia).
