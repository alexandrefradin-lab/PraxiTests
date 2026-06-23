# Le Sanctuaire de l'Attention (plugin `praxizenith`)

Mini-app PraxiQuest (`type: mini-app`) de **La Salle du Trésor**. Un parcours de **60 jours pour apprendre à se concentrer** : **un exercice d'attention concret par jour**, du plus simple au plus exigeant, avec un micro-défi à appliquer le jour même.

## Principe

- **Déblocage dans La Salle du Trésor** : le Sanctuaire est un *cadeau* débloqué quand l'utilisateur atteint **2400 Éclats** cumulés (bloc `reward` du manifest → `RewardCatalog`). Tant que le seuil n'est pas atteint, la route est verrouillée.
- **Cadence jour par jour** *(une fois entré)* : à la première visite, le parcours démarre (`focus_journeys.started_on`). L'exercice du jour J se débloque J-1 jours après. Le jour 1 est dispo tout de suite, le jour 2 le lendemain, etc. Les jours passés restent accessibles (rattrapage) ; les jours futurs sont verrouillés.
- **Récompense** : chaque exercice appliqué rapporte **+15 Éclats** (via `GamificationEngine`), octroyés une seule fois.
- **8 blocs thématiques progressifs** : comprendre son attention · aménager l'environnement · entraîner le muscle de l'attention · single-tasking · attention prolongée · dompter la distraction · travail profond · durer.
- Suivi : compteur d'exercices faits, **série de jours d'affilée** (streak), ressenti 1-5 + notes par exercice.

## Les 8 blocs (60 jours)

| Jours | Bloc |
|---|---|
| J1-8 | Comprendre ton attention (diagnostic) |
| J9-16 | Aménager le sanctuaire (environnement) |
| J17-24 | Le muscle de l'attention (entraînement de base) |
| J25-32 | Une chose à la fois (single-tasking) |
| J33-40 | Tenir la durée (attention prolongée) |
| J41-48 | Dompter la distraction |
| J49-56 | Les profondeurs (deep work) |
| J57-60 | Durer (régénérer & intégrer) |

## Routes (`/sanctuaire`)

| Route | Nom | Rôle |
|---|---|---|
| `GET /sanctuaire` | `praxizenith.index` | Tableau de bord + timeline 60 jours |
| `GET /sanctuaire/jour/{day}` | `praxizenith.show` | Un exercice (verrou 403 si non débloqué) |
| `POST /sanctuaire/jour/{day}/done` | `praxizenith.complete` | Marquer comme appliqué |

## Activation (sur le serveur)

```bash
composer dump-autoload -o
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate praxizenith   # lance migrate + seed (60 exercices)
```

> ⚠️ Build Vite **sur Windows** (les 2 pages Vue sont dans `resources/js/Pages/`), puis commit de `public/build` — pas de npm/node sur OVH (cf. workflow de déploiement).

## Contenu

Les 60 exercices sont dans `src/Data/Exercises.php` (modifiables, puis re-seed via `db:seed`). Chaque entrée : `day`, `theme`, `title`, `summary`, `body` (markdown), `micro_challenge`, `duration_min`, `icon`.

Parti pris : exercices étayés par la recherche sur l'attention (entraînement attentionnel & pleine conscience — réseaux attentionnels de Posner ; coût de la bascule de tâches — Monsell, Rubinstein & Meyer ; travail profond — Newport ; intentions d'implémentation — Gollwitzer ; restauration de l'attention par la nature — Kaplan ; rythmes ultradiens — Kleitman ; surfer l'impulsion — Marlatt). Chaque exercice privilégie un geste concret et applicable plutôt que la théorie. Aucun n'est un substitut à un avis médical (TDAH, troubles de l'attention : voir un professionnel).
