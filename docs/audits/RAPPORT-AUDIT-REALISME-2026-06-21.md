# Audit de réalisme des résultats — PraxiQuest

**Date :** 21 juin 2026
**Périmètre :** les 12 moteurs de scoring + le moteur générique et l'étalonnage commun (`NormInterpreter`).
**Objet :** vérifier que les scores produits sont *plausibles et crédibles* — formules, gestion des items inversés, bornes atteignables, seuils d'interprétation, et conformité aux instruments scientifiques de référence.
**Méthode :** lecture du code de chaque moteur et de ses données, confrontation aux normes publiées (Karasek/SUMER, Maslach MBI, Big Five/NEO, RIASEC/Holland, Schwartz/ESS, Bar-On), et **simulation numérique** des cas limites pour quantifier les biais.

> ⚠️ Les seuils « officiels » cités (notamment MBI) proviennent de la littérature psychométrique standard, pas d'un document du dépôt. À reconfirmer sur les manuels avant toute communication publique.

---

## 1. Résumé exécutif

Le socle technique est correct : les sommes, normalisations et inversions sont, dans la majorité des cas, mathématiquement justes, et le moteur d'étalonnage `NormInterpreter` (CDF normale, recalcul dynamique au-delà de 50 passations) est bien conçu. **Le problème n'est pas la mécanique de calcul, mais le réalisme et la validité des résultats restitués.** Cinq faiblesses systémiques reviennent et fragilisent la crédibilité des restitutions, en particulier sur les tests « cliniques » (burnout, intelligence émotionnelle) où une fausse précision peut induire en erreur.

Classement des tests par niveau de confiance dans le réalisme du résultat :

| Niveau | Tests | Lecture |
|---|---|---|
| 🟢 **Crédible** (socle sain) | RIASEC (PraxiMet) | Échelle + sommation + normes externes plausibles ; réserves mineures |
| 🟠 **À recalibrer** (résultat exploitable mais biaisé) | Big Five (PraxiMum), Schwartz (PraxiValeurs), EQ-i (PraxiEmo) | Calculs justes mais calibrage/normalisation qui déforment le profil |
| 🔴 **Réalisme compromis** (le résultat ne mesure pas ce qu'il prétend) | **MBI (PraxiCare)**, **360° (Praxis360)**, **PraxiSpeak** | Échelle non conforme / dispositif manquant / mesure d'assiduité déguisée |
| ⚪ **Auto-évaluation gamifiée** (à ne pas présenter comme psychométrie) | PraxiSelf, PraxiZen, PraxiFlow, PraxiLink | Outils de coaching ; seuils arbitraires, pas de normes |

---

## 2. Cinq problèmes systémiques

**P1 — Seuils d'interprétation arbitraires.** La quasi-totalité des bandes (faible/moyen/élevé, niveaux, wellness, QE…) sont des coupures rondes décidées à la main (85/70/55/40, 120/200/280…), sans distribution empirique derrière. Deux personnes très proches peuvent basculer de part et d'autre d'une frontière et recevoir des étiquettes opposées.

**P2 — Normes manquantes pour 8 tests sur 12.** Seuls RIASEC, EQ-i, Schwartz et Big Five ont des normes seedées (et encore : ce sont des moyennes de littérature recopiées, pas des étalonnages plateforme). Pour tous les autres, `NormInterpreter::enrich()` retombe sur `fallback()` → **percentile et label `null`** : le composant « niveau » est inerte, seul le score brut 0-100 s'affiche. L'auto-étalonnage futur (≥50 passations) se fera sur une population auto-sélectionnée, non représentative.

**P3 — Biais d'acquiescement non corrigé.** Hors PraxiZen, **aucun** test d'auto-évaluation ne gère d'items inversés. Tous les items étant formulés positivement, répondre « plutôt d'accord » partout produit un score élevé. *Simulation EQ-i :* répondre « 3/4 » à tout = **240/320 = « QE Élevé »** sans aucune introspection.

**P4 — Mesures de contrôle calculées mais non actives.** PraxiEmo calcule un score de désirabilité sociale… mais ne s'en sert que pour afficher un avertissement : il **ne corrige pas** les scores. Big Five calcule la désirabilité et lève une alerte ≥75 % sans pondérer non plus.

**P5 — Appellations trompeuses.** Trois produits portent le nom d'un instrument de référence qu'ils n'implémentent pas : « MBI » sur une échelle 4 points au lieu de 0-6, « EQ-i » (marque MHS propriétaire, 133 items) sur un questionnaire maison de 80 items, et « 360° » sur une **auto-évaluation mono-répondant** sans le moindre regard externe.

---

## 3. Fiches par test

### 🔴 PraxiCare — Karasek + MBI (burnout)

Le plus sensible, car un test de burnout mal calibré a des conséquences humaines.

- **Échelle MBI non conforme.** Le MBI officiel est une échelle de *fréquence* à 7 points (0 = jamais … 6 = chaque jour). Le code utilise une échelle d'*intensité* à 4 points (1-4) reconvertie en 0-3. Maxima écrasés : EE 27 au lieu de 54, DP 15 au lieu de 30, AP 24 au lieu de 48. **Toute comparaison aux seuils Maslach devient invalide.**
- **Seuils de sévérité trop restrictifs → sous-détection du burnout.** *Simulation :* le seuil « EE élevé » codé (≥19/27 = **70 %** du max) exige un mal-être bien plus marqué que Maslach (≥27/54 = **50 %**) — soit **+20 points** de pourcentage. Pire sur la dépersonnalisation : ≥10/15 = **67 %** codé contre **33 %** chez Maslach, **+33 points**. Le test classera « non burnout » des personnes que l'instrument de référence classerait « élevé » (faux négatifs).
- **AP inversé** : techniquement correct (`3 - val`), mais le résultat expose un `ap` où *élevé = mauvais*, à l'inverse de la convention Maslach — piège pour la couche d'affichage et les futurs développeurs.
- **Karasek** : seuils en valeurs absolues sur sommes brutes non pondérées (demandes ≥ 22, latitude > 21, soutien 21/10), non alignés sur les médianes de population (type SUMER). Le seuil latitude tombe quasiment au milieu de plage → classement « job strain » potentiellement décalé.

### 🟠 PraxiMum — Big Five

- **Amplification artificielle des T-scores (`×1,15`).** Une étape « parité WP » dilate les T autour de 50. *Simulation :* à +1,2 σ, le percentile passe de p88 à **p92** ; effet de **+3 à +4 points de percentile** dans la zone médiane-haute (l'effet est masqué aux extrêmes par le clamp). Sans justification psychométrique, tout positionnement est légèrement surévalué.
- **Compression de variance → « tout le monde au milieu ».** La dimension est la moyenne de 6 facettes elles-mêmes clampées. *Simulation (2000 profils) :* écart-type des dimensions ≈ **4,6 points T** seulement, et **76 % des dimensions tombent dans la bande « moyen » (T 45-55)**. Résultat peu discriminant.
- **Archétype instable.** La clé d'archétype bascule à T ≥ 50 ; comme les dimensions gravitent autour de 50, un écart de 1 point change une lettre entière, et le repli sur distance de Hamming **plaque toujours** un des 16 profils. Deux passations quasi identiques peuvent donner deux archétypes différents.
- Normes de facettes (mean/sd) plausibles ; désirabilité calculée mais non corrective (P4).

### 🟠 PraxiValeurs — Schwartz

- **Effet plafond / pas de correction ipsative.** Schwartz mesure des *priorités* relatives et impose un centrage intra-individuel. Ici, 4 items positifs en Likert 1-6 sans centrage : la plupart des valeurs ressortent à 70-100 %. On mesure un **niveau d'approbation général**, pas une hiérarchie de valeurs.
- **Pondération « tournoi » 60/40 écrasante** : quand un tournoi de paires existe, la composante normalise sur le max de victoires → le gagnant est *toujours* à 100 % et le perdant à 0 %, ce qui peut déplacer une dimension de ±40 points par un mécanisme non psychométrique.
- Normes ESS Wave 9 plausibles ; socle crédible **si** la correction ipsative est ajoutée.

### 🟠 PraxiEmo — EQ-i

- **Bandes globales arbitraires et non équidistantes** (120/200/280 sur 80-320). *Simulation :* « 2 » partout = 160 = Modéré ; « 3 » partout = 240 = **Élevé**. Une réponse globalement positive suffit à décrocher « QE Élevé ».
- **Appellation « EQ-i » trompeuse** (marque propriétaire ≠ instrument maison) ; **normes jamais seedées** → étalonnage inactif au lancement.
- Désirabilité (Marlowe-Crowne) calculée mais **non corrective** (P4) ; axe de développement à seuil absolu (≤12/20) qui peut n'afficher aucun axe sur un profil homogène.

### 🟢 PraxiMet — RIASEC

- Socle sain : binaire Oui/Non, sommation par type, code Holland à 3 lettres, normes Holland/INETOP plausibles.
- Réserve : **deux scores coexistent pour une même dimension** — la barre `brut/14×100` et le percentile étalonné — qui peuvent diverger visiblement. Choisir lequel afficher. Percentiles saturés aux extrêmes (p99 dès 14/14).

### 🔴 Praxis360 — « 360° »

- **Ce n'est pas un 360°** : auto-évaluation mono-répondant (documenté dans le code), sans branche pairs/manager/collaborateurs. La valeur centrale d'un 360 — l'écart entre auto-perception et regards externes — est absente.
- Normes volontairement à `null` (honnête) → aucun étalonnage avant 50 passations ; normalisation linéaire sans garde-fou de désirabilité → auto-complaisance non corrigée.

### 🔴 PraxiSpeak — prise de parole

- **Le score mesure le taux de complétion d'exercices** (cases « fait » booléennes), **pas une compétence d'orateur.** Tout cocher = 100 = « Orateur expert ». Score bimodal (≈0 ou ≈100). Le « niveau orateur » est trompeur.

### ⚪ PraxiSelf / PraxiZen / PraxiFlow / PraxiLink

- Outils d'auto-évaluation / coaching. Communs : seuils arbitraires (P1), pas de normes (P2), pas d'items inversés sauf **PraxiZen** (qui les gère correctement).
- **PraxiFlow** : des chiffres présentés comme normatifs (« tu fais partie des 20 % qui… », « ×1,5 d'exécution », citation Cal Newport) **sans aucune donnée** derrière — à retirer ou sourcer.
- **PraxiLink** : le moteur exécuté n'est qu'un Likert auto-déclaré ; toute la machinerie de correction (QCM, Kendall-tau, OSBD, ~400 lignes) est **du code mort** (`computeScores()` privée jamais appelée). Soit l'activer, soit la supprimer.

---

## 4. Tableau de synthèse priorisé

| # | Test | Constat | Gravité | Effort correctif |
|---|---|---|---|---|
| 1 | PraxiCare/MBI | Échelle 4 pts + seuils 0-3 → sous-détection du burnout (faux négatifs) | 🔴 Critique | Moyen |
| 2 | Praxis360 | Auto-évaluation présentée comme « 360° » | 🔴 Élevée | Élevé (ou renommer) |
| 3 | PraxiSpeak | Mesure d'assiduité présentée comme compétence | 🔴 Élevée | Élevé |
| 4 | PraxiEmo | Bandes QE arbitraires + « EQ-i » trompeur + normes absentes | 🟠 Moyenne | Faible-Moyen |
| 5 | PraxiMum | Amplification ×1,15 + variance écrasée + archétype instable | 🟠 Moyenne | Faible |
| 6 | PraxiValeurs | Effet plafond, pas de correction ipsative | 🟠 Moyenne | Moyen |
| 7 | Transverse | Biais d'acquiescement (pas d'items inversés) | 🟠 Moyenne | Moyen |
| 8 | Transverse | Normes absentes pour 8/12 → percentile null | 🟠 Moyenne | Moyen |
| 9 | PraxiFlow | Statistiques « normatives » inventées | 🟠 Moyenne | Faible |
| 10 | PraxiMet | Double score divergent par dimension | 🟡 Mineure | Faible |
| 11 | PraxiLink | ~400 lignes de scoring en code mort | 🟡 Mineure (dette) | Faible |

---

## 5. Recommandations

**Priorité immédiate (réalisme & responsabilité).**
1. **MBI** : soit passer à l'échelle officielle 0-6 et aux seuils Maslach, soit retirer la terminologie clinique (« burnout », « MBI », sévérité) et présenter le test comme un *auto-questionnaire de vécu au travail* non diagnostique. Dans tous les cas, ajouter un disclaimer explicite.
2. **Praxis360** et **PraxiSpeak** : renommer honnêtement. « 360° » → « auto-bilan de leadership » tant qu'il n'y a pas de multi-répondants ; PraxiSpeak → « parcours d'entraînement » (progression), pas « niveau d'orateur ».
3. Retirer les chiffres normatifs non sourcés de **PraxiFlow**.

**Priorité moyenne (calibrage).**
4. **Big Five** : supprimer l'amplification ×1,15 (ou la documenter/justifier) et revoir le double clamp qui écrase la variance ; stabiliser l'archétype (hystérésis ou bandes de confiance plutôt qu'une frontière à 50).
5. **Schwartz** : ajouter la correction ipsative (centrage sur la moyenne intra-individu) et reconsidérer le poids du tournoi.
6. **EQ-i** : remplacer les bandes globales linéaires par des bandes adossées à la distribution réelle une fois les normes disponibles ; renommer pour éviter la confusion avec l'EQ-i 2.0.

**Priorité de fond (fiabilité statistique).**
7. Introduire des **items inversés** (ou un indice d'acquiescement correctif) dans les auto-évaluations.
8. **Activer/seeder les normes** pour les tests dépourvus, et afficher clairement « étalonnage en cours » tant que N < 50 ; afficher le percentile *ou* le brut, pas deux scores contradictoires.
9. Nettoyer le **code mort** de PraxiLink.

---

*Aucun fichier du projet n'a été modifié pour produire cet audit. Les constats quantitatifs (amplification Big Five, compression de variance, bandes EQ-i, calibrage MBI) ont été vérifiés par simulation numérique.*
