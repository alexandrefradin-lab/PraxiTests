# Audit complet PraxiQuest — 16 juillet 2026

> 4 axes en parallèle : **Sécurité** · **Ergonomie/UX** · **Fonctionnalités** · **Partie admin**
> Base : Laravel 11 + Inertia + Vue 3, architecture plugin-first, déploiement OVH.
> Chaque constat est vérifié dans le code réel (fichier:ligne).

---

## Verdict global

Le socle est **sain et au-dessus de la moyenne** à ce stade : architecture plugin-first réellement respectée, sécurité durcie (tous les correctifs de juin tiennent, aucune régression), tests psychométriques bien couverts. Les problèmes restants se concentrent sur **trois zones** :

1. **La monétisation par Éclats** — le gating des mini-apps premium est contournable par URL directe (remonté par 2 audits indépendants).
2. **Des features branchées à moitié** — tips du jour cassés, séquences email inertes, consultation pro incohérente avec la promesse de consentement.
3. **Deux bugs bloquants côté candidat** — loader IA infini sur les résultats, Armurerie inaccessible au clavier.

---

## 🔴 Bloquants (à corriger avant tout)

| # | Axe | Constat | Fichier | Fix |
|---|-----|---------|---------|-----|
| B1 | Fonc. | Bouton « Je l'applique aujourd'hui » : route `dailytip.apply` inexistante (vraie route `daily.tip.apply`) → Ziggy jette au clic, tips + streak cassés | `resources/js/Components/DailyTipCard.vue:29` | 1 ligne |
| B2 | UX | Loader IA infini sur les résultats core : le poller vit dans CandidateLayout mais l'écran `ai_pending` s'affiche hors layout → jamais monté | `resources/js/Pages/Candidate/ResultsShow.vue:198` | reprendre le polling local de `Grimoire.vue` (gère `failed`/`stuck` + Réessayer) |
| B3 | UX/a11y | Armurerie inaccessible au clavier : cartes en `<div @click>` sans role/tabindex, CTA en `<span pointer-events:none>` → aucun test lançable au clavier/lecteur d'écran | `resources/js/Pages/Candidate/TestsIndex.vue:170` | envelopper dans `<Link>` |

## 🟠 Majeurs

### Sécurité
- **M2 — Bypass 2FA candidat.** Un candidat peut activer le 2FA mais le défi TOTP au login n'est déclenché que pour admin/professional → second facteur silencieusement ignoré. `AuthController.php:38`. Fix : retirer la condition de rôle.
- **M3 — Secret TOTP en clair en base.** `two_factor_secret` sans cast `encrypted` → une exfiltration de la base livre tous les secrets à privilèges. `User.php:29-35`. Fix : `'two_factor_secret' => 'encrypted'` + migration.
- **M1 / 2.2 — Gating Éclats contournable (remonté 2×).** Seule `index()` vérifie le palier ; `show()`/`complete()` et `JourneyDashboardController` ne vérifient rien. Un candidat accède au premium par URL directe et farme +15 Éclats/jour sans franchir le paywall. `RewardCatalog.php:84-91,183,200`, `JourneyDashboardController.php:25-27`, mini-apps `show/complete`. Fix : `isJourneyUnlocked(slug)` + `guard()` partagé appelé dans `show/complete/index` et `LibraryController::sealed`.

### Fonctionnalités
- **Séquences email : moteur complet, zéro carburant.** `SequenceRunner`, jobs, listener câblés, mais aucun `EmailSequence::create`, aucune UI, aucun seeder → jamais déclenchable. Fix : CRUD admin minimal, ou seeder de rétention, ou retirer le câblage.
- **Rattachement invitation→tentative fragile.** `pending_invitation_id` en session + `verified` exigé sur les routes candidat → si le candidat vérifie son email depuis un autre appareil, la session est perdue et le suivi pro casse en silence. Fix : persister l'ID sur le user, ou marquer l'email vérifié pour les inscriptions par token.
- **Incohérence consentement / consultation pro.** La case à l'inscription promet le « partage des résultats avec le professionnel », mais le consentement ne débloque aucune vue résultat individuelle pour le pro (`ConseillerDashboardController:104-108` n'affiche que 8 synthèses). Fix : clarifier la promesse ou ouvrir une vue résultat pro conditionnée au consentement.
- **Zéro test sur les zones à risque.** RewardCatalog/gating, parcours 60j, tips, billing, invitations, 360, séquences email : aucune couverture — les bugs B1 et M1 auraient été détectés. Fix : 3 tests Feature ciblés (accès parcours sans Éclats, POST tip, invitation→tentative).

### Ergonomie
- **« Voir ma Révélation » jamais rendu** : template teste `already_attempted.result_id`, contrôleur envoie un booléen. `TestShow.vue:166`.
- **Échec de sauvegarde de réponse silencieux** pendant la passation (`onError` vide). `AttemptPlay.vue:113`.
- **Double-submit** possible en fin d'épreuve et au démarrage de test (pas de verrou). `AttemptPlay.vue:104`, `TestShow.vue:20`.
- **Thème Corporate écrasé + tutoiement hardcodé** dans passation/résultats/onboarding : les tokens parchemin sont redéfinis en dur → un client corporate voit du parchemin doré là où tout le reste est marine/blanc. `AttemptPlay.vue:492`, `ResultsShow.vue:323`, `Onboarding.vue`.
- **Rater360 : réponses perdues sans feedback** — un évaluateur externe anonyme peut soumettre une éval incomplète en la croyant remplie. `Rater360.vue:44`.
- **Panel 360 impraticable sur mobile** : formulaire flex sans wrap (~80 px/champ sur 375 px) + erreurs de validation invisibles. `Panel.vue:113`.
- **Contraste AA échoué sur le CTA principal** : `.ac-btn-primary` texte crème sur dégradé or = 2,3–3,3:1. `app.css:142`. Fix : encre foncée sur or (~9:1).

### Admin (audit séparé — déjà partiellement traité)
- Menu à plat de 13 entrées **→ corrigé** (sidebar groupée 5 sections).
- 4 tables « personnes » jamais reliées, pas de fiche Utilisateur 360° — **à faire (Lot 2b)**.
- Éditeur de tests : scoring JSON brut avalé en `null` **→ corrigé** ; drag & drop et éditeur structuré restent à faire (Lot 2c).

## 🟡 Mineurs (backlog)

- **Sécurité** : CSP en Report-Only + `unsafe-inline` (M4, défense en profondeur) ; sanitisation prompt-injection par regex (B1) ; email en query string register/reset (B2) ; SSRF théorique via base_url IA admin (B3).
- **Fonctionnalités** : `AttemptComplete.vue` orphelin + route fantôme ; 5 modèles `JourneyProgress` morts dans les plugins ; modèle `JourneyNudgeResponse` mort ; whitelist de page de passation codée en dur ; TODO ARC-M1 (`Artisan::call` dans `onActivate`) dupliqué ×32 → risque timeout activation OVH ; template plugin bundlé en prod.
- **Ergonomie** : redirection forcée 4 s non skippable ; coquille « Complete ton profil » → « Complète » ; échelles 360 en 9,6 px ; WelcomeModal sans focus-trap/Échap ; ShareProfileButton sans confirmation de révocation ; page d'erreur unique et générique ; radar SVG dupliqué au lieu du composant standard.
- **Dette racine** : 78 fichiers `vite.config.js.timestamp-*.mjs`, scripts `_check*.mjs`/`_phpbal.cjs`, `_imports/` (5,1 Mo), `praxisens/` dupliqué, prototypes HTML, ~16 docs d'audit à ranger sous `docs/audits/`. Risque : envoi de débris en prod via pscp.

---

## Plan d'action recommandé

**Lot 1 — Colmatage (rapide, fort rendement)**
1. B1 tips (1 ligne) + B2 poller IA + B3 accessibilité Armurerie.
2. M2 (bypass 2FA) + M3 (secret TOTP chiffré).
3. Bugs UX 1-5 : prop `result_id`, `onError` de sauvegarde, verrous double-submit.

**Lot 2 — Anti-fraude & cohérence produit**
4. Gating Éclats : `guard()` partagé + `isJourneyUnlocked` (résout M1/2.2).
5. Rattachement invitation→tentative hors session + trancher consentement/consultation pro.
6. 3 tests Feature anti-régression.

**Lot 3 — Crédibilité & conformité**
7. Thème Corporate (retirer les tokens locaux) + vouvoiement.
8. Contraste AA du CTA et des textes muted.
9. Séquences email : activer ou retirer.

**Lot 4 — Hygiène**
10. Purge dette racine + modèles morts + `.gitignore`.

**Admin (chantier parallèle déjà entamé)** : sidebar groupée ✅, quick wins ✅ → fiche Utilisateur 360° → TestEditor v2.

---

## Suivi des corrections (mis à jour le 16/07/2026)

### ✅ Lot 1 — Colmatage (fait)
- B1 tip du jour (nom de route) · B2 loader IA (poller local + états échec/retry) · B3 Armurerie clavier.
- Bugs UX : lien « Voir ma Révélation » (prop id), échec de sauvegarde non silencieux, double-submit verrouillé (start + complete).
- M2 bypass 2FA candidat (défi TOTP pour tout compte 2FA) · M3 secret TOTP chiffré (`encrypted` + migration `2026_07_16_120001`).

### ✅ Lot 2 — Anti-fraude & cohérence (fait)
- **Gating Éclats colmaté** : `isJourneyUnlocked` / `pluginUnlockRedirect` dans `RewardCatalog` ; garde ajoutée à `JourneyDashboardController` (index/show/complete), `PracticeController`, `FocusController`, `MirrorController` (show/complete), `LibraryController::sealed`.
- **Rattachement invitation** : inscription via token → email auto-vérifié (l'adresse est prouvée par le lien) → plus de fenêtre cross-device.
- **Vue résultat pro** : `ResultController::authorizeAttempt` ouvre la restitution au pro invitant si `consent_share_professional` ; lien conditionnel dans le dashboard conseiller.
- **Tests** : `tests/Feature/Lot2SecurityTest.php` (gating locked/unlocked, nom route tip, invitation→lien+vérif, résultat pro consenti/refusé/autre compte). ⚠️ à exécuter là où `vendor/` + DB sont présents.

### ⏳ Reste (Lot 3-4)
- Thème Corporate (tokens locaux à retirer) + vouvoiement · contraste AA du CTA · séquences email (activer/retirer) · purge dette racine.
