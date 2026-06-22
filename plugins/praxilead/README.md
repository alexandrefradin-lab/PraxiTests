# Le Cap — 60 jours de management (plugin `praxilead`)

Mini-app PraxiQuest (`type: mini-app`). Un parcours de **60 jours** pour le manager de proximité : **une bonne pratique concrète par jour**, avec un micro-défi à appliquer le jour même.

## Principe

- **Cadence jour par jour** : à la première visite, le parcours démarre (`mgmt_journeys.started_on`). La pratique du jour J se débloque J-1 jours après. Le jour 1 est dispo tout de suite, le jour 2 le lendemain, etc. Les jours passés restent accessibles (rattrapage) ; les jours futurs sont verrouillés.
- **Pas de gating par Éclats** : la seule clé est le temps. Mais chaque pratique appliquée rapporte **+15 Éclats** (via `GamificationEngine`), octroyés une seule fois.
- **8 blocs thématiques** : poser le cadre · écouter & 1:1 · feedback · déléguer · motiver · faire grandir · tensions & décisions · sens & durer.
- Suivi : compteur de pratiques faites, **série de jours d'affilée** (streak), ressenti 1-5 + notes par pratique.

## Routes (`/management`)

| Route | Nom | Rôle |
|---|---|---|
| `GET /management` | `praxilead.index` | Tableau de bord + timeline 60 jours |
| `GET /management/jour/{day}` | `praxilead.show` | Une pratique (verrou 403 si non débloquée) |
| `POST /management/jour/{day}/done` | `praxilead.complete` | Marquer comme appliquée |

## Activation (sur le serveur)

```bash
composer dump-autoload -o
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate praxilead   # lance migrate + seed (60 pratiques)
```

> ⚠️ Build Vite **sur Windows** (les 2 pages Vue sont dans `resources/js/Pages/`), puis commit de `public/build` — pas de npm/node sur OVH (cf. workflow de déploiement).

## Contenu

Les 60 pratiques sont dans `src/Data/Practices.php` (modifiables, puis re-seed via `db:seed`). Chaque entrée : `day`, `theme`, `title`, `summary`, `body` (markdown), `micro_challenge`, `duration_min`, `icon`.
