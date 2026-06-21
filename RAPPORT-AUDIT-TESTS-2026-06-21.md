# Rapport d'audit des 11 tests PraxiQuest — 2026-06-21

Audit de cohérence, scoring (résultats) et bugs techniques sur les 11 plugins de test.
Méthode : lecture intégrale `src/Data`, `src/Scoring`, `database/seeders`, `routes`, `resources/js/*Result.vue` de chaque plugin, + analyse du cœur (`TestEngine`, `AttemptPlay.vue`). Aucun fichier modifié.

## ✅ Corrections appliquées (2026-06-21)

Tous les problèmes ci-dessous ont été corrigés dans le code. Lint PHP 8.1 OK, 0 octet nul, logique simulée numériquement.

- **praxicare** : scoring MBI converti 1-4 → 0-3 (`val - 1`). Plages restaurées (EE 0-27, DP 0-15 avec « faible » de nouveau atteignable, AP 0-24 avec « élevé » de nouveau atteignable). Karasek inchangé. *(KarasekMbiScoringEngine.php)*
- **praxilink** : converti en questionnaire d'auto-évaluation jouable — nouveau `Data/Questions.php` (20 items, 5 dimensions, échelle 1-5), seeder réécrit (Test/Section/Question `scale`), `score()` simplifié en moyenne par dimension réutilisant les helpers existants (style dominant, interprétation, forces/axes). plugin.json aligné. *(le contenu interactif d'origine — QCM/OSBD/classement — est abandonné, choix validé)*
- **praxiself** : question `likert` → `scale`, options au format `max/min_label/max_label`. *(ExercisesSeeder.php)*
- **praxispeak** : question `exercise` → `single` (Oui/Non), `required:true` pour fiabiliser le score. *(ExercisesSeeder.php)*
- **praxivaleurs** : normalisation `((moy-1)/5)*100` au lieu de `(moy/6)*100` — le bas de l'échelle n'est plus écrasé à 17 %. *(SchwartzScoringEngine.php)*
- **praxizen** : sélection d'exercices en 2 passes (garantit 3 exos) + `currentStreak()` dédupliqué par jour calendaire. *(PraxiZenScoringEngine.php, JourneyProgress.php)*
- **praxiflow** : 4 catégories `procrastination` → `lutte_procrastination`. **praximum** : clés du fallback d'archétype alignées (`nom/tagline/couleur1/2/rarete/traits`).
- **praxis360** : aucun correctif — faux positif de l'audit (`enrich()` utilise `key()` = `praxis360-softskills`, exactement la clé semée ; étalonnage cohérent).

> Note technique : le shell sandbox servait par moments une vue périmée/corrompue (octets nuls en fin de fichier) des fichiers écrits via l'outil fichier. Deux seeders réellement corrompus (praxilink, praxiself) ont été réécrits proprement via le shell. Comme le build se fait sur le serveur OVH, lance un `php -l` / build serveur comme dernier filet après déploiement.

---

## Contrat système (vérifié dans le cœur)

`resources/js/Pages/Candidate/AttemptPlay.vue` ne rend que **4 types** de question : `single`, `scale`, `multi`/`multiple`, `text`.
Pour `type: 'scale'`, le rendu est `v-for="n in options.max"` → il émet **toujours des entiers 1..options.max, jamais 0**. Tout moteur qui suppose une échelle commençant à 0 est donc décalé.

---

## Tableau de synthèse

| Test | État | Sévérité max | Problème principal |
|------|------|--------------|--------------------|
| **praxicare** | ⛔ Résultats faux | CRITIQUE | Échelle MBI 1-4 vs scoring 0-3 → diagnostic burnout invalide |
| **praxilink** | ⛔ Casse le site | CRITIQUE | Interface inexistante + modèle de données incompatible (désactivé) |
| **praxiself** | ⛔ Injouable | MAJEUR | `type:'likert'` non rendu par le front |
| **praxispeak** | ⛔ Injouable | MAJEUR | `type:'exercise'` non rendu par le front |
| **praxivaleurs** | ⚠️ Résultats biaisés | MAJEUR | Normalisation `/6` au lieu de `/5` → plancher à 17 % |
| **praxizen** | ⚠️ À corriger | MAJEUR | Sélection d'exercices peut rendre < 3 ; `currentStreak()` gonflé |
| **praxis360** | ⚠️ À vérifier | MAJEUR | Clé de jointure des normes (`test_slug`) probablement désalignée |
| **praxiemo** | ✅ Sain | mineur | Défaut silencieux sur réponse manquante |
| **praximet** | ✅ Sain | mineur | (RIASEC binaire, `single`) — RAS notable |
| **praximum** | ✅ Sain | mineur | Fallback archétype = code mort incohérent ; alerte DS quasi inatteignable |
| **praxivaleurs** | voir ci-dessus | — | — |

Bilan : **4 tests bloquants/faux** (praxicare, praxilink, praxiself, praxispeak), **3 à corriger** (praxivaleurs, praxizen, praxis360), **3 sains** (praxiemo, praximet, praximum).

---

## Problèmes critiques

### praxicare — le diagnostic burnout (MBI) est faux  [CRITIQUE]
Le seeder sème les items MBI en `type:'scale'`, `options.max=4` → le front envoie 1..4.
Mais `KarasekMbiScoringEngine` attend 0-3 (il clampe `max(0,min(3,val))`, défaut 0). Conséquences chiffrées :

- **EE (épuisement)** : plancher réel 9 au lieu de 0 ; la valeur 4 (« Toujours ») écrasée à 3. Score gonflé, sévérité (`severite($ee,10,18)`) mal calibrée.
- **DP (dépersonnalisation)** : plancher 5 alors que le seuil « faible » est ≤ 4 → **le niveau « faible » est impossible à obtenir**, même en répondant « Jamais » partout.
- **AP (accomplissement)** : formule `3 - val` plafonne AP à **16/24** ; le seuil « élevé » (> 16) est **inatteignable**. La jauge `ap/24` du `Result.vue` ne peut jamais se remplir.

La partie **Karasek est saine** (échelle 1-4 alignée, D4 inversé géré une fois, profils atteignables).
Correctif recommandé : garder `options.max=4` et passer le scoring MBI sur une base 1-4 (EE/DP : `max(1,min(4,val))`, maxes 36/20 ; AP : `5-val`, max 32) puis recalibrer tous les seuils `severite()`.

### praxilink — plante le site entier à l'activation  [CRITIQUE]
`PraxiLinkScoringEngine` fait `implements ScoringEngineInterface` — **cette interface n'existe pas** (le cœur n'expose que `...\Contracts\ScoringEngineContract`), et la signature est `score(array $answers, ...)` au lieu de `score(TestAttempt): array`. À l'activation, le `boot()` du plugin plante → 500 sur tout le site et tout `artisan` (déjà documenté dans `RESOLUTION-500-TESTS-2026-06-20.md`, plugin désactivé en base).
Corollaire : son seeder écrit dans une table `plugin_exercises` (clé string `ea-01`) sans créer `Test`/`TestSection`/`TestQuestion` ; même l'interface corrigée, le scoring lirait des questions inexistantes. **Réécriture complète sur le modèle Test/Section/Question requise**, pas juste l'interface.

---

## Problèmes majeurs

### praxiself & praxispeak — injouables (type de question non supporté)  [MAJEUR]
- praxiself sème `type:'likert'` ; praxispeak sème `type:'exercise'`. Le front ne connaît ni l'un ni l'autre → **aucun input ne s'affiche, le test est impassable**.
- praxiself : en plus, `options` est un tableau d'objets `{value,label}` alors que le front `scale` lit `options.max`/`options.min_label`. Correctif : `type:'scale'` + `options:['max'=>5,'min_label'=>…,'max_label'=>…]`. Le scoring est sain une fois jouable.
- praxispeak : le scoring interprète `answer.value` en booléen « fait/pas fait » et les questions sont `required:false` ; le dénominateur du score dépend de ce qui a été ouvert (fragile). Correctif : re-seeder en `scale`/`single`, ou ajouter un composant front pour ce type.

### praxivaleurs — bas de l'échelle écrasé  [MAJEUR]
`SchwartzScoringEngine` normalise `($moy / 6) * 100` sur une échelle 1-6 qui n'émet jamais 0. La réponse « Aucune importance » (valeur 1) donne donc **17 %, pas 0 %** ; tout score de dimension est planché à ~17 %. Correctif : `(($moy - 1) / 5) * 100` (et/ou renommer le label min).

### praxizen — programme tronqué + streak gonflé  [MAJEUR]
- `PraxiZenScoringEngine` (sélection d'exercices) : la garde de diversité `|| count($selected) < 2` est neutralisée et un item peut être sauté sans remplacement → le « programme personnalisé » peut **rendre 1 ou 2 exercices au lieu de 3**. Pas de crash. Correctif : sélection en 2 passes.
- `JourneyProgress::currentStreak()` : `diffInDays()` absolu + pas de déduplication par jour → streak surévalué. Le scoring lui-même (échelle 1-4, 5 dimensions × 4 items) est sain.

### praxis360 — étalonnage potentiellement mort  [MAJEUR]
Les normes sont insérées avec `test_slug='praxis360-softskills'` (clé du moteur), mais le `Test` a `slug='praxis360'`. Si `NormInterpreter` interroge par le slug du test, la jointure ne matche jamais → percentiles/labels jamais produits (fallback % brut côté front, donc pas d'erreur visible). À confirmer sur la signature de `NormInterpreter`, puis aligner la clé. Le scoring (échelle 1-5 alignée, 6×6 items, inversion correcte) est **impeccable**.

---

## Points mineurs (robustesse, non bloquants)

- **praxiemo / praxiflow / praxizen / praxiself** : réponse manquante → valeur par défaut silencieuse (`?? 1` ou `?? 0`) au lieu de signaler. Sans impact (items `required`) mais masque les anomalies.
- **praximum** : fallback d'archétype renvoie des clés (`titre/description/couleur`) incohérentes avec celles attendues par le front (`nom/tagline/couleur1…`) — code mort non atteignable à nettoyer ; alerte désirabilité `>=75 %` quasi inatteignable (plafond réel 75 %).
- **praxiflow** : 4 exercices étiquetés `category:'procrastination'` au lieu de `'lutte_procrastination'` (le scoring utilise l'autre champ, donc OK) ; commentaires de phases du parcours décalés.
- **praxispeak** : description annonce 20 exercices, il y en a 22.
- **praxizen / praxis360** : libellés « 60 jours » / « 360° » partiellement nominaux par rapport à ce qui est réellement mesuré.

---

## Recommandation d'ordre de traitement

1. **praxilink** — garder désactivé (risque 500 global) jusqu'à réécriture.
2. **praxicare** — corriger le scoring MBI (résultats actuellement faux mais test affiché comme fonctionnel : le plus trompeur).
3. **praxiself + praxispeak** — corriger le type de question (injouables).
4. **praxivaleurs + praxizen + praxis360** — corrections de calibrage/jointure.
5. Mineurs au fil de l'eau.
