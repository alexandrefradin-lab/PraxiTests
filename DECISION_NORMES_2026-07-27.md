# Note de décision — Étalonnage des normes psychométriques

**Date** : 2026-07-27
**Statut** : ⏳ décision requise (produit + juridique) avant tout correctif technique lié aux normes
**Enjeu** : c'est le risque n°1 de l'audit complet. Il conditionne les items #1 et #2 du plan d'action.

---

## Le problème en une phrase

Les restitutions de PraxiQuest affichent des **percentiles et notes T présentés comme normatifs**, calculés à partir de normes (`TestNormsSeeder.php`) qui **citent des sources académiques réelles avec des tailles d'échantillon précises** (Holland/INETOP N≈2400, Bar-On N≈1800, ESS Wave 9 N≈2025, Rolland/NEO-PI-R) — mais ces valeurs **ne proviennent d'aucune de ces publications** et **aucun échantillon de référence n'existe dans le projet**. Ce sont des estimations internes plausibles habillées en normes validées.

## Pourquoi c'est grave

1. **Scientifiquement** : les instruments PraxiQuest sont des adaptations maison (nombre d'items, échelle, langue, structure dimensionnelle différents des tests cités). Les normes publiées de ces sources **ne sont pas transférables**. Tout percentile affiché est donc invalide.
2. **Juridiquement** : dans un contexte de **bilan de compétences** (activité réglementée), présenter un score comme normé sur « Bar-On (2002) adapté France · N≈1800 » alors que ce n'est pas le cas est une **allégation trompeuse**. Les disclaimers (« pas un QI », « indépendant de l'EQ-i 2.0® ») réduisent le risque mais ne le couvrent pas : la citation chiffrée reste affirmée.
3. **Commercialement** : c'est l'argument de crédibilité du produit. S'il s'effondre à l'examen d'un psychologue du travail ou d'un OPCO, c'est la confiance sur tout le produit qui tombe.

### Aggravants techniques déjà identifiés
- **Big Five** normé `mean=50, sd=10` « par définition » → normalisation **circulaire** (on norme le T-score sur lui-même).
- **EQi** = 16 dimensions maison ≠ 15 de Bar-On → l'étiquette « Bar-On adapté » est inexacte.
- **Schwartz** : scores ipsatifs comparés à des normes d'approbation → distorsion systématique de tous les profils.
- Le filet « ça se recalcule automatiquement à 50 passations » est **cassé pour Big Five** et **non planifié** pour plusieurs tests → les normes ne s'auto-corrigeront pas.

### Ce qui est déjà bien fait (à généraliser)
`praxisens` (normes `null` assumées) et `praxicog` (« hypothèse interne / provisoire ») sont **honnêtes** : ils n'affichent pas de percentile qu'ils ne peuvent pas justifier. C'est le modèle de référence.

---

## Les 3 options

### Option 1 — Honnêteté immédiate (recommandée à court terme)
Retirer les fausses citations et tailles d'échantillon du `TestNormsSeeder`, remplacer par « **barème indicatif provisoire — non étalonné sur population de référence** », et **masquer tout percentile / note T / rang** dans les restitutions tant que N réel est insuffisant. On garde les **scores bruts** et les **labels qualitatifs** (bas/moyen/élevé) présentés comme repères internes, pas comme position dans une population.

- **Coût** : faible (quelques jours). Modifs seeder + conditionner l'affichage des percentiles + relire les libellés de restitution.
- **Effet** : supprime le risque juridique et scientifique **immédiatement**. Cohérent avec praxisens/praxicog.
- **Contrepartie** : la restitution paraît moins « scientifique » (plus de percentile). À assumer dans le discours commercial (« repères qualitatifs, étalonnage en cours »).

### Option 2 — Étalonnage réel (cible à moyen terme)
Constituer un **échantillon de référence documenté** (population active FR représentative, N≥200-300 par sous-groupe), calculer de vraies normes + indices de fiabilité (alpha de Cronbach), et documenter la démarche (manuel technique).

- **Coût** : élevé (mois, budget collecte de données, éventuellement un psychométricien).
- **Effet** : crédibilité réelle et défendable. C'est la seule voie pour afficher des percentiles à bon droit.
- **Prérequis techniques** : corriger d'abord le filet de recalcul (clé `scores_dim`), relever le seuil N, distinguer « norme externe » vs « référence interne plateforme », et **ne pas** coter les réponses manquantes au minimum (biais).

### Option 3 — Statu quo (déconseillée)
Garder les normes actuelles. **Risque juridique et réputationnel non maîtrisé.** À écarter.

---

## Recommandation

**Faire l'Option 1 maintenant** (mise en conformité immédiate, faible coût) **puis engager l'Option 2** comme chantier de fond si le produit vise une crédibilité psychométrique forte. Les deux ne s'opposent pas : l'Option 1 est le comportement honnête pendant que l'Option 2 se construit.

**Tant que la décision n'est pas prise, ne pas corriger le filet de recalcul Big Five (#2 de l'audit)** : si on choisit l'Option 1 (masquer les percentiles), ce correctif devient secondaire ; si on choisit l'Option 2, il faut le faire **avec** le relèvement du seuil et la correction des manquants, pas isolément.

---

## Questions à trancher

1. On assume commercialement une restitution **sans percentile** à court terme (Option 1) ?
2. Y a-t-il un budget / une volonté pour un **vrai étalonnage** (Option 2), et sur quel horizon ?
3. Qui porte la responsabilité méthodologique (psychométricien référent) si on maintient des scores normés ?
