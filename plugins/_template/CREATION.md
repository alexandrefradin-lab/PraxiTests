# Créer un nouveau plugin PraxiQuest

## 1. Copier le template

```bash
cp -r plugins/_template plugins/mon-plugin
```

## 2. Chercher-remplacer dans tous les fichiers

| Placeholder | Remplacer par | Exemple |
|-------------|--------------|---------|
| `PLUGIN_SLUG` | Slug kebab-case | `mon-plugin` |
| `PLUGIN_CLASS` | PascalCase | `MonPlugin` |
| `PLUGIN_NAME` | Nom lisible | `Mon Test` |

Sur Windows (VS Code) : Ctrl+Shift+H dans le dossier `plugins/mon-plugin/`

Renommer aussi le fichier :
```
plugins/mon-plugin/src/Scoring/PLUGIN_CLASSScoringEngine.php
  → plugins/mon-plugin/src/Scoring/MonPluginScoringEngine.php

plugins/mon-plugin/resources/js/Pages/PLUGIN_CLASSResult.vue
  → plugins/mon-plugin/resources/js/Pages/MonPluginResult.vue
```

## 3. Déclarer dans composer.json

```json
"autoload": {
    "psr-4": {
        "Praxis\\Plugins\\MonPlugin\\": "plugins/mon-plugin/src/",
        "Praxis\\Plugins\\MonPlugin\\Database\\Seeders\\": "plugins/mon-plugin/database/seeders/"
    },
    "classmap": [
        "plugins/mon-plugin/PluginServiceProvider.php"
    ]
}
```

Puis :
```bash
composer dump-autoload
```

## 4. Remplir le contenu

### Questions (`src/Data/Questions.php`)
- Lister toutes les questions dans `all()`
- Définir les dimensions dans `dimensions()`
- Pour les tests > 30 questions : créer un `questions.json` et le charger en statique

### Scoring Engine (`src/Scoring/MonPluginScoringEngine.php`)
- La méthode `score()` reçoit le `TestAttempt` avec toutes les réponses
- Elle doit retourner un array avec au minimum :
  - `engine` : clé du moteur
  - `dimensions` : `{ dim_key: score_0_100 }` — utilisé par ResultsShow.vue
  - `norm_scores` : enrichi par `NormInterpreter::enrich()` — dots + labels
  - `meta` : libellés des dimensions

### Échelle de validité (désirabilité sociale) — utiliser le service core
- **Ne jamais recoder** le mécanisme : utiliser `Praxis\Core\Scoring\SocialDesirability`
  (trois implémentations dupliquées ont divergé avant la factorisation de juillet 2026).
- Items de contrôle style Marlowe-Crowne (comportements humains normaux que
  presque tout le monde admet avoir PARFOIS ; une admission basse = biais) :

```php
$ds = SocialDesirability::fromControlSum(
    sum: $ctrlSum, answered: $ctrlCount,
    itemCount: 6, itemMax: 5,            // adapter à l'échelle du test
    messageFort: "…message de restitution contextualisé…",
);
$shrink = SocialDesirability::shrinkFactor($ds['niveau']);
if ($shrink < 1.0) {
    $avg = SocialDesirability::shrink($avg, $midpoint, $shrink); // régression douce
}
```

- Les seuils sont **proportionnels à l'échelle** (fort ≤ 1/3, modéré ≤ 2/3 de la
  plage d'admission) — ne pas copier des seuils absolus d'un autre plugin.
- Exposer le résultat sous la clé `desirabilite` (`{score, niveau, alerte, message}`)
  pour la restitution front.
- Références : `praxiemo/src/Scoring/EqiScoringEngine.php`, `praxisens/src/Scoring/PraxiSensScoringEngine.php`.

### Normes (`database/seeders/NormsSeeder.php`)
- Renseigner `mean` et `std_dev` par dimension dans les unités de `raw_scores`
- Indiquer la `source` (référence bibliographique ou "À calculer")
- Si pas de normes publiées : laisser null → le système les calculera après 50 passations

### Page résultats (`resources/js/Pages/MonPluginResult.vue`)
- Utiliser **uniquement** les classes `pt-*` et les variables `--pt-*`
- Ne jamais hardcoder de couleurs Tailwind (`bg-indigo-600` etc.)
- La page hérite automatiquement du thème premium

## 5. Activer le plugin

Via l'interface admin (Admin > Plugins) ou Artisan :

```bash
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate mon-plugin
```

L'activation exécute automatiquement `QuestionsSeeder` et `NormsSeeder`.

## 6. Déclarer le scoring engine dans la DB

Le scoring engine doit correspondre au champ `scoring_engine` du test en DB.
Le seeder crée le test avec le bon slug automatiquement.

## Checklist finale

- [ ] PLUGIN_SLUG, PLUGIN_CLASS, PLUGIN_NAME remplacés partout
- [ ] Fichiers renommés (ScoringEngine + Vue)
- [ ] composer.json mis à jour + `composer dump-autoload`
- [ ] Questions remplies dans `Questions::all()`
- [ ] Dimensions définies dans `Questions::dimensions()`
- [ ] Normes renseignées dans `NormsSeeder` (ou null si inconnues)
- [ ] Page Vue utilise uniquement `pt-*` et `var(--pt-*)`
- [ ] Plugin activé via admin ou Artisan
- [ ] Test visible et passable dans l'espace candidat
