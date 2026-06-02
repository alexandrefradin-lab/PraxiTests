# `_imports/` — Zone de réception des plugins WordPress

Ce dossier sert à **importer puis convertir** des plugins WordPress existants en plugins PraxiQuest natifs.

## Comment déposer

Choisis l'une des options :

### Option 1 — Zip directement

```
_imports/
├── plugin-test-rh.zip
├── plugin-orientation-pro.zip
└── ...
```

Je décompresserai chaque zip dans `_imports/extracted/{slug}/` puis je lancerai la conversion.

### Option 2 — Dossiers décompressés

```
_imports/
├── plugin-test-rh/
│   ├── plugin-test-rh.php
│   ├── includes/
│   ├── admin/
│   ├── public/
│   ├── assets/
│   └── README.txt
└── plugin-orientation-pro/
    └── ...
```

### Option 3 — URL téléchargeable

Donne-moi simplement les URLs (GitHub, ZIP public, etc.) — je les rapatrierai.

## Ce dont j'ai besoin pour chaque plugin

Crée à côté de chaque plugin un fichier `_meta.md` avec :

```md
# Plugin : Nom du plugin

## Test
- **Nom du test** : ...
- **Type** : questionnaire / situationnel / projectif
- **Nombre de questions** : ...
- **Durée estimée** : ... min
- **Public cible** : RH / candidat / manager / tous

## Scoring
- **Approche** : RIASEC / MBTI / DISC / Big Five / custom
- **Dimensions** : analytique, créatif, ... (liste)
- **Calcul** : moyenne / somme pondérée / typologie / autre
- **Restitution** : score brut / pourcentage / profil typé / lettre

## Restitution
- **Format actuel** : page WP / PDF / email / shortcode
- **Contenu** : graphique radar / barres / texte narratif / métiers suggérés

## Spécificités WordPress
- **Hooks utilisés** : (laisser vide si tu ne sais pas, je les détecterai)
- **Custom post types** : ...
- **Shortcodes** : `[mon_test]` ...
- **Tables custom** : `wp_xxx_yyy`
- **Dépendances** : ACF, WooCommerce, autre plugin ?

## À garder / améliorer
- Garder tel quel : ...
- À adapter : ...
- À supprimer : ...
```

Si tu n'as pas le temps de remplir ça, je peux **inférer** depuis le code — mais ça prendra plus de temps et risque d'erreurs.

## Ce que je vais faire pour chaque plugin

1. **Décompresser** dans `_imports/extracted/{slug}/`
2. **Analyser** le code (PHP, JS, CSS, manifest WP)
3. **Extraire** la logique métier (questions, scoring, restitution)
4. **Générer** dans `plugins/{slug}/` :
   - `plugin.json` (manifest PraxiQuest)
   - `PluginServiceProvider.php`
   - Migrations dédiées (questions, scoring config)
   - `ScoringEngine` custom si formule spécifique
   - Pages Vue avec **design system PraxiQuest** appliqué (`pt-card`, `pt-btn-primary`, gradient indigo→emerald, etc.)
   - Hooks PraxiQuest (`attempt.completed`, `jobs.suggested`, ...)
5. **Tests Pest** : scoring + flow complétion
6. **Doc d'usage** dans `plugins/{slug}/README.md`

## Mapping WordPress → PraxiQuest

Voir `_imports/MAPPING.md` pour la table de correspondance complète.

---

> **Sécurité** : ce dossier n'est PAS scanné par l'auto-discover des plugins. Les imports restent isolés tant qu'ils n'ont pas été convertis et copiés dans `plugins/`.
