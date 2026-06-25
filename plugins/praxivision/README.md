# praxivision — L'Éveilleur

Mini-app PraxiQuest : **60 jours de leadership intégral**, 1 pratique par jour.

## Déploiement

```bash
composer dump-autoload -o
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate praxivision
```

Puis build Vite sur Windows et commit `public/build`.

## Structure

- **8 blocs** : Se connaître · Présence & énergie · Vision & sens · Influence & conviction · L'équipe comme système · Décider sous incertitude · Transformer l'organisation · Durer & transmettre
- **Tables** : `vision_practices`, `vision_journeys`, `vision_practice_progress`
- **Routes** : `/leadership` (prefix), noms `praxivision.*`
- **Salle du Trésor** : seuil 1 500 Éclats
- **Éclats** : +20 par pratique intégrée (vs +15 pour praxilead)

## Thème couleur

Bleu nuit `#1e3a5f` / `#2d5986` (distinct du doré praxilead).
