# Audit complet PraxiQuest — 2026-07-27

**Périmètre** : sécurité · fonctionnalités · incohérences/anomalies · ergonomie/UX/UI · étalonnage psychométrique · couverture des tests.
**Méthode** : 5 audits spécialisés parallèles, lecture du code réel (419 fichiers PHP `app/` + `plugins/`, ~84 vues Vue/Inertia, moteurs de scoring, seeders de normes, routes, tests, CI).
**Stack constatée** : Laravel + Inertia.js + Vue 3 (SPA, pas Blade sauf e-mails/PDF/404), MySQL en prod (OVH), SQLite `:memory:` en test.

---

## Verdict global

L'ingénierie logicielle est **solide et mature** : sécurité durcie par plusieurs passes (aucune faille critique applicative), robustesse du moteur de scoring générique, a11y sérieuse, états de chargement/erreur soignés, tests existants rigoureux. Le repo porte les traces d'audits antérieurs bien appliqués.

**Les risques majeurs ne sont pas dans le code, ils sont ailleurs :**
1. **Validité psychométrique** — les normes affichées comme scientifiques sont fabriquées ; c'est le risque n°1 (scientifique, commercial, juridique) d'un produit de bilan de compétences.
2. **Hygiène du dépôt et provisioning** — legacy tracké, bug de migration qui casse toute installation fraîche.
3. **Filet de sécurité (tests + CI)** — excellent en profondeur, minuscule en largeur ; le paiement, l'admin et la sécurité du compte sont à nu, et le déploiement n'est pas bloqué par la CI.

---

## Tableau de priorisation transverse (à traiter dans cet ordre)

| # | Gravité | Domaine | Constat | Fichier |
|---|---|---|---|---|
| 1 | **CRITIQUE** | Psychométrie | Normes RIASEC/EQi/Schwartz/Big Five **inventées mais habillées de citations réelles + N** ; aucun échantillon dans le repo → tout percentile/note T affiché est invalide | `database/seeders/TestNormsSeeder.php:8-111` |
| 2 | **CRITIQUE** | Psychométrie | Le filet « auto-recalcul à 50 passations » est **cassé pour Big Five** (clé `scores_dim` non reconnue) et **non planifié** pour praxicog/praxisens/… → normes figées à vie | `NormInterpreter.php:206-221`, `routes/console.php:39-51` |
| 3 | **CRITIQUE / BLOQUANT** | Anomalie | Migration `profile_shares` datée **2024** → FK vers `users` avant sa création → `migrate` frais **casse en MySQL** (masqué par la CI SQLite) | `database/migrations/2024_01_01_000010_create_profile_shares_table.php:13` |
| 4 | **CRITIQUE** | Tests/CI | **Zéro test** sur Billing/webhook Stripe (revenu, bascule LIVE en attente) ; déploiement **non bloqué** par la CI (`--min=5` symbolique, `deploy-ovh.ps1` indépendant) | `BillingController.php`, `ci.yml`, `deploy-ovh.ps1` |
| 5 | **ÉLEVÉ** | Sécurité | Le **secret TOTP (graine 2FA)** transite en clair vers `api.qrserver.com` à l'enrôlement | `app/Services/TotpService.php:59-63` |
| 6 | **ÉLEVÉ** | Psychométrie | EQi : **réponses manquantes cotées au minimum (1)** → passation incomplète tirée vers « QE Faible » + pollue le recalcul des normes | `plugins/praxiemo/src/Scoring/EqiScoringEngine.php:33` |
| 7 | **ÉLEVÉ** | Psychométrie | **Mismatch d'échelle Schwartz** : scores ipsatifs comparés à des normes d'approbation → tout le monde gonflé sur « pouvoir », écrasé sur « autonomie » | `SchwartzScoringEngine.php:48-76` vs `TestNormsSeeder.php:80-97` |
| 8 | **ÉLEVÉ** | Psychométrie | **Aucune mesure de fiabilité** (0 occurrence de Cronbach/alpha) ni garde de complétion minimale | `app/`, `plugins/` |
| 9 | **ÉLEVÉ** | Anomalie | **`.gitignore` inopérant** : `_imports/` (110) + `plugins/_wp_import/` (114) legacy WordPress **trackés**, dont un ZIP binaire | `.gitignore:32-33` |
| 10 | **ÉLEVÉ** | Anomalie | `praxispeak` : `$attempt->id % count($quotes)` **sans garde** → `DivisionByZeroError` si liste vide | `PraxiSpeakScoringEngine.php:83` |
| 11 | **ÉLEVÉ** | Tests | Aucun test : **2FA**, reset mot de passe, rate-limiting, tout le **back-office Admin**, **360°/jetons évaluateurs**, **11/17 moteurs de scoring** | `tests/` |
| 12 | **ÉLEVÉ** | UX/UI | Parcours **Corporate non tenu bout-en-bout** : `AttemptPlay.vue` et `ResultsShow.vue` **redéfinissent les tokens en hex codés en dur** → thème corporate court-circuité (+ 18/26 pages plugins) | `AttemptPlay.vue:567-583`, `ResultsShow.vue:378-397` |
| 13 | **ÉLEVÉ** | UX/UI | Tokens sémantiques `--pt-danger/-warning/-info/-success/-indigo` **définis nulle part** → couleurs Tailwind brutes hors palette (`#e5e7eb` ×56) sur les restitutions plugins | `plugins/*/resources/js/Pages/*Result.vue` |
| 14 | **ÉLEVÉ** | Tests | **Parité SQLite(test) ↔ MySQL(prod)** non assurée alors que les bugs déjà corrigés sont des violations de contrainte au niveau moteur | `phpunit.xml`, `TestCase.php` |

---

## 1. Sécurité — posture solide, pas de faille critique

Vérifiés **sûrs** : IDOR (ownership contrôlé partout : Attempt, Result, Panel360, Grimoire, listes admin cloisonnées multi-tenant), mass assignment (tous les modèles en `$fillable` explicite, `User` exclut `two_factor_secret`), upload CV (magic bytes + hors webroot), injection SQL (bindings paramétrés, tri par allowlist), webhook Stripe (signature Cashier, montant/plan jamais issus du client), secrets (aucune clé en dur, aucun `dd`/`env()` hors config), RGPD (export rate-limité, suppression irréversible sous mot de passe), 2FA (secret chiffré au repos, codes de récupération hashés SHA-256, comparaison timing-safe).

**À corriger :**
- **ÉLEVÉ** — `TotpService.php:59-63` : secret 2FA envoyé à `api.qrserver.com`. → Générer le QR **localement** (`bacon/bacon-qr-code` / `endroid/qr-code`).
- **MOYEN** — `AuthController.php:216-218` : **énumération d'emails** au reset (message différencié). → Réponse générique unique.
- **MOYEN** — `OracleChat.vue:304` : `v-html` sur l'historique IA — confirmer l'échappement (self-XSS aujourd'hui). Le sink public `MarkdownText.vue` est **sûr** (échappement + validation de schéma d'URL).
- **MOYEN** — 2FA **non obligatoire pour les admins** (`routes/admin.php`). → Forcer l'enrôlement pour le rôle `admin`.
- **FAIBLE** — Throttle login par IP sans lockout par compte ; énumération à l'inscription ; kill-switch `require_email_verification` à garder à `true` en prod.

## 2. Étalonnage psychométrique — le maillon faible (voir #1, #2, #6, #7, #8)

Au-delà du tableau transverse :
- **CRITIQUE (validité)** — Big Five `mean=50, sd=10` posé « par définition » → normalisation **circulaire** (on norme le T-score sur lui-même). EQi = 16 dimensions maison ≠ 15 de Bar-On → citation « Bar-On adapté » trompeuse.
- **ÉLEVÉ** — Recalcul plateforme : seuil **N=50 trop faible** (usage : ≥200-300/sous-groupe), population **auto-sélectionnée** non représentative, et `recompute()` **écrase** la provenance (« Platform users — auto-computed »).
- **MOYEN** — Big Five : facette partiellement répondue → somme brute vs norme de facette complète → **T sous-évalué** (`BigFiveScoringEngine.php:35-65`).
- **MOYEN** — MBI **compressé 7→4 points**, cutoffs Maslach reproportionnés à la main ; quadrants Karasek découpés au **milieu d'échelle** et non à la médiane populationnelle (`KarasekMbiScoringEngine.php`). Bien disclaimés, mais tout affichage de « sévérité » reste injustifié.
- **MOYEN** — EQi : **aucun item inversé** (80 items tous positifs) → biais d'acquiescement non contrôlé.
- **Bonnes pratiques à généraliser** : `SocialDesirability` (seuils en proportion, shrink raisonnable) est bien conçu ; `praxisens` (normes `null` assumées) et `praxicog` (« provisoire ») sont **honnêtes** — c'est ce modèle qu'il faut étendre au `TestNormsSeeder` central.

> **Recommandation stratégique** : soit conduire un vrai étalonnage (échantillon documenté), soit **retirer les fausses citations et les N**, marquer « barème provisoire, non étalonné » et **masquer les percentiles/niveaux** tant que N réel insuffisant. En l'état, les restitutions chiffrées sont scientifiquement indéfendables et juridiquement exposées.

## 3. Anomalies / incohérences

- **BLOQUANT** — migration `profile_shares` 2024 (voir #3). → Renommer `2026_04_27_000013_*`.
- **IMPORTANT** — legacy tracké (voir #9) + résidus de debug commités : `_checktip*.mjs`, `_checkvue.mjs`, `_phpbal.cjs`, `_perm_test`, `ORACLE_DEBUG.md`, `_vite_build.log`, 5 `.fuse_hidden*` dans des dossiers de migrations. → `git rm -r --cached …` + commit.
- **IMPORTANT** — `// TODO ARC-M1` répété dans ~30 `PluginServiceProvider::onActivate()` : `Artisan::call(migrate+seed)` **synchrone dans le cycle HTTP** → risque de timeout OVH 60 s à l'activation d'un plugin.
- **IMPORTANT** — **3 schémas de manifest `plugin.json` incompatibles** coexistent (clé de scoring racine vs bloc `test:` vs bloc `reward:`) → aucun consommateur générique uniforme. praxiflow (moteur/vue **orphelins** « plus de test »), praximet (`slug` ≠ `test.slug`), `loadViewsFrom` vers des dossiers absents (praximet/praxiself/praxilink/praxispeak/praxizen).
- **MOYEN** — 4 plugins appellent `NormInterpreter::enrich()` **sans livrer de `NormsSeeder`** → étalonnage jamais actif (fallback silencieux).
- **MINEUR** — modulo/division sans garde homogène ; RIASEC normalisé sur `max=14` codé en dur ; 3 paires de migrations au même timestamp ; pas d'enum central pour les statuts.
- **RAS (sain)** : routes→contrôleurs 100 % valides, clés `config('praxiquest.*')` toutes présentes, aucun `catch {}` vide ni `dd`/`console.log` dans le code vivant.
- **Note** : `praxicog` est **enregistré dans `composer.json` mais non commité** (travail en cours non versionné). `vite.config.js.timestamp-*` (~90) sont bien gitignorés → simple `git clean -fX`.

## 4. Ergonomie / UX / UI — socle excellent, cohérence de thème à finir

Points forts : onboarding 1 étape avec repli, passation sans cul-de-sac (verrou anti-double-POST, erreurs réseau remontées, retour arrière), restitution avec états échec/trop-long, états vides partout, a11y sérieuse (skip-link, focus-trap, `role`/`aria`, `prefers-reduced-motion`, `lang="fr"`), honeypot anti-bot, responsive (drawer mobile, `clamp()`).

**À corriger :**
- **MAJEUR** — thème Corporate non tenu (voir #12, #13) + copie d'onboarding **médiévale en dur** non basculée via `L` → cadre en mode Corporate vit une expérience mixte dissonante.
- **MOYEN (a11y)** — **contraste insuffisant** : or `#A67520` ≈ 3.2:1 et `--text-muted` ≈ 3.0:1 sur parchemin, utilisés pour des **petits textes** (< 4.5:1 WCAG AA). → Basculer le petit texte or sur `--color-primary-dark`, assombrir `--text-muted`.
- **MOYEN** — formulaires : erreurs et cases à cocher **recodées inline** au lieu des classes DS existantes `.pt-error`/`.ac-error`/`.ac-checkbox`. Validation client quasi absente (concordance mots de passe seulement après aller-retour serveur).
- **MOYEN** — jargon médiéval dense (Grimoire, Codex, Serments, Oracle) **non explicité** ni traduit en Corporate. → Glossaire/infobulles.
- **MINEUR** — boutons-icône Oracle/mot de passe sans `aria-label` ; overlay Level-Up sans focus-trap/Échap ; toast `role="alert"` + `aria-live` contradictoire ; pas de garde `beforeunload` en test (impact limité, sauvegarde serveur).

## 5. Couverture des tests — profonde mais étroite

Couverture de ligne mesurée par la CI : **6,7 %**. Les tests existants sont **rigoureux** (IDOR réels, cas négatifs, idempotence, régressions ancrées sur des commits) et couvrent le plus critique (passation, RGPD, upload, badges, easter eggs). Mais des pans entiers sont **à nu** :

- **CRITIQUE** — Billing/webhook Stripe (voir #4).
- **ÉLEVÉ** — 2FA/reset/rate-limiting, back-office Admin (12 contrôleurs), 360°/jetons évaluateurs, **11/17 moteurs de scoring** (praxibiais, praxicog, praxiflow, praxifocus, praxilink, praxis360, praxiself, praxispeak, praxitempo, praxizen).
- **ÉLEVÉ** — parité SQLite↔MySQL (voir #14) : lancer un job CI matriciel **MySQL** (les doublons de badges corrigés sont un comportement moteur non reproduit par SQLite).
- **ÉLEVÉ / CI** — `--min=5` symbolique, jobs lint/security en `continue-on-error`, déploiement `deploy-ovh.ps1` **non gated**. → Branch protection GitHub (check `tests` requis) + conditionner le déploiement au succès CI + relever `--min` progressivement.
- **MOYEN** — aucun test de **réponses manquantes/incomplètes** (d'où B1/B2 psychométriques invisibles), ni de l'étalonnage (`enrich/recompute`) ; `GamificationEngineTest:31` skip conditionnel + assertion tautologique ; factories manquantes (fixtures dupliquées à la main partout) ; chaîne de découverte/activation des plugins non testée.

---

## Plan d'action recommandé (séquencé)

**Sprint 1 — bloquants & risque produit**
1. Renommer la migration `profile_shares` → débloque tout provisioning MySQL frais.
2. Décision produit sur les normes : étalonner **ou** retirer les fausses citations + masquer les percentiles non étalonnés (#1).
3. Corriger `extractScore()` (clé `scores_dim`) et généraliser le scheduler de recalcul (#2).
4. Purger le legacy tracké et les résidus de debug (`git rm --cached`) (#9).
5. Garde anti-division praxispeak (#10).

**Sprint 2 — sécurité & fiabilité des scores**
6. QR TOTP local (#5) ; réponse générique au reset (énumération) ; 2FA obligatoire admin.
7. EQi : proratiser les manquants au lieu de coter 1 (#6) ; garde de complétion minimale globale ; corriger l'échelle Schwartz (#7).
8. Tests Billing/Stripe + matrice d'autorisation Admin/360° + job CI MySQL + branch protection (#4, #11, #14).

**Sprint 3 — cohérence & finition**
9. Définir les tokens `--pt-*` dans app.css (Parchemin **et** Corporate) ; supprimer les tokens hex locaux d'AttemptPlay/ResultsShow ; localiser l'onboarding via `L` (#12, #13).
10. Contrastes WCAG AA ; recentraliser les formulaires sur le DS.
11. Unifier le schéma de manifest plugin ; purger praxiflow/praxilink orphelins ; introduire un `NormsSeeder` là où `enrich()` est appelé.
12. Introduire une mesure de fiabilité (alpha) et une politique unifiée des réponses manquantes (#8).
