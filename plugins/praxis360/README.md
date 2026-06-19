# Praxis 360 — Évaluation soft skills

Module PraxiQuest porté depuis le plugin WordPress **Praxis 360** (Praxis Accompagnement).

## Source

- WordPress : `plugins/_wp_import/praxis-360/` v1.0.0
- Référentiel d'items : `includes/class-praxis360-items.php`
- Logique de scoring : `includes/class-praxis360-scoring.php`

## Caractéristiques

- **36 items** d'auto-évaluation (6 dimensions × 6 items)
- **Échelle de fréquence à 5 points** : 1 Presque jamais · 2 Rarement · 3 Parfois · 4 Souvent · 5 Presque toujours
- **Aucun item inversé** : tous les items sont formulés positivement
- **Scoring** : moyenne simple des items par dimension (1-5), normalisée 0-100, + indice global et forces / axes de progrès

### Dimensions (soft skills)

| Clé | Libellé |
|-----|---------|
| `communication`  | Communication |
| `collaboration`  | Collaboration & esprit d'équipe |
| `adaptabilite`   | Adaptabilité |
| `relation`       | Intelligence relationnelle / empathie |
| `fiabilite`      | Fiabilité & sens des responsabilités |
| `leadership`     | Leadership d'influence |

## ⚠️ Caveat — multi-évaluateurs non porté

Le plugin WordPress d'origine est un **vrai 360°** : auto-évaluation **+** manager **+** pairs **+** collaborateurs **+** clients/partenaires, avec invitations par email, seuil d'anonymat (≥ 3 répondants par catégorie agrégée), agrégation « regard des autres », calcul des **écarts d'auto-perception** (others − self), détection des **angles morts**, et questions ouvertes (verbatims).

Le moteur PraxiQuest actuel est **mono-candidat** (un seul répondant par tentative). Seule la branche **auto-évaluation** (formulation à la 1re personne) est donc portée ici, au format PraxiQuest standard.

Les fonctionnalités multi-rater **ne sont pas portables telles quelles** et nécessiteront une extension dédiée :
- invitations / collecte multi-répondants par campagne ;
- anonymisation (seuil de 3) ;
- comparaison self / autres, écarts, angles morts ;
- verbatims des questions ouvertes.

Le scoring engine expose déjà `strengths` / `improvements` (calculés sur l'auto-évaluation seule) pour préfigurer cette restitution.

## Normes

Aucune norme statistique publiée dans la source (test maison). `mean` / `std_dev` sont à `null` (`source = "À calculer"`) ; l'auto-étalonnage prendra le relais après 50 passations.

## Activation

```bash
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate praxis360
```

L'activation exécute automatiquement `QuestionsSeeder` et `NormsSeeder`.
