# PraxiMet — Plugin RIASEC

Test d'orientation Holland RIASEC porté depuis WordPress vers PraxiTests.

## Caractéristiques

- 84 questions binaires (oui/non)
- 6 types : Réaliste · Investigateur · Artistique · Social · Entreprenant · Conventionnel
- 2 sous-domaines par type × 7 questions
- Code 3 lettres dominant (Holland standard)
- Génération automatique de leads qualifiés

## Activation

```bash
php artisan praxitests:plugins:discover --sync
php artisan praxitests:plugins:activate praximet
```

L'activation lance la migration + le seeder de questions automatiquement.

## Hooks consommés

- `attempt.completed` — crée un lead qualifié dans `leads` avec source `praximet-riasec`

## Restitution

Page Vue dédiée `PraximetResult.vue` qui affiche :
- Code 3 lettres avec couleurs typées
- 6 dimensions normalisées (0-100%)
- Détail par sous-domaine
- Métiers IA suggérés (si IA activée)
- Synthèse IA (si activée)

## Origine

Porté depuis WordPress plugin `praximet` v2.1.7.
