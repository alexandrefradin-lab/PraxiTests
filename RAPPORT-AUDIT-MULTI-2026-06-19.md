# Rapport d'audit multi-agents — PraxiQuest

**Date :** 19 juin 2026 · **Méthode :** 6 audits en parallèle (debug 500, QA, technique, fonctionnel, sécurité, ergonomie), lecture seule du code.
**Focus prioritaire :** erreurs 500 à répétition.

---

## 1. Synthèse exécutive

Le produit est mûr : le cœur (passation, plugins, IA synthèse + 15 métiers, onboarding, restitution, RGPD) est implémenté et de bonne facture. Les audits convergent cependant sur **un même foyer de 500 : le flux de vérification d'email à moitié câblé**, et sur **une dette de déploiement non résolue** (repo ≠ serveur, 3 copies, patchs à la main) qui réintroduit les bugs à chaque mise en prod.

Trois constats reviennent dans **plusieurs** audits indépendamment — ce sont les certitudes :

1. **Middleware `verified` incohérent** : retiré de `web.php` (car la route `verification.notice` n'existe pas → 500) mais **toujours présent dans `routes/admin.php`** (lignes 14 et 25). → 500 garanti pour tout compte non vérifié sur l'espace admin/conseiller. *(debug, technique, fonctionnel, sécurité)*
2. **`diffInMonths()` non corrigé dans `OnboardingController::update()` (ligne 83)** : même bug Carbon 3 que celui déjà corrigé dans `store()`, oublié dans l'édition de profil. → 500 à la mise à jour du profil. *(debug)*
3. **Dette de déploiement** : `deploy-server.sh` fait `git checkout --` sur 3 fichiers patchés à la main côté OVH → divergence repo/serveur jamais résolue ; 3 copies de l'app encore présentes. *(technique, + DEBUG_500_RESOLUTION.md)*

---

## 2. PRIORITÉ 1 — Éradiquer les 500 (à faire en premier)

| # | Tâche | Fichier(s) | Pourquoi 500 |
|---|-------|-----------|--------------|
| 500-1 | Retirer `verified` de `routes/admin.php` (l.14, 25) — ou implémenter la route `verification.notice` | `routes/admin.php` | `RouteNotFoundException` pour tout user non vérifié sur `/admin/*` |
| 500-2 | Neutraliser l'envoi d'email de vérification au `register` : try/catch, ou `markEmailAsVerified()`, ou retirer `implements MustVerifyEmail` tant que le flux n'est pas fini | `Auth/AuthController.php:67-72`, `Models/User.php:16` | Exception SMTP (OVH mal configuré) remontée dans POST /register en queue `sync` |
| 500-3 | Corriger `status_months` : `(int) abs(now()->diffInMonths($data['status_since']))` | `Candidate/OnboardingController.php:83` | Float signé Carbon 3 refusé par colonne `unsignedSmallInteger` |
| 500-4 | Charger `routes/profile_share.php` dans le routing (ou supprimer l'accessor `share_url`) | `bootstrap/app.php`, `Models/ProfileShare.php:76` | `route('profile.shared')` inexistante → exception dès sérialisation d'un partage |
| 500-5 | Ajouter try/catch dans `ProfileSynthesisService` et `JobSuggestionService` (comme `CvExtractionService`) + marquer le résultat en échec | `Core/AI/Services/*.php` | Sans ça, échec IA → polling `ai_pending` infini (perçu comme « ça plante ») |
| 500-6 | Dispatcher `ExtractCvDataJob` en `->afterResponse()` (l.51 et 105) | `Candidate/OnboardingController.php` | En `sync`, toute exception du job remonte dans la requête onboarding |
| 500-7 | Corriger les routes Ziggy inexistantes dans les pages résultats plugins | `plugins/praxiself/.../PraxiSelfResult.vue`, `plugins/praxilink/.../PraxiLinkResult.vue` | `route('dashboard')`, `route('exercises.show')`… → page de restitution cassée |

**Assainir le déploiement (sinon les 500 reviennent) :**
- 500-8 : faire du **repo git la seule source de vérité** — reporter dans le repo les 3 fichiers patchés à la main sur OVH, puis **supprimer le `git checkout --`** de `deploy-server.sh` (l.26-30).
- 500-9 : sur le serveur, **supprimer/archiver les copies obsolètes** `~/www/PraxiTests`, `~/praxitests` et les marqueurs `_which.txt`.
- 500-10 : **versionner des configs framework sûres** (`config/queue.php`, `cache.php`, `session.php`, `database.php`, `mail.php`) avec défauts OVH (file/sync) — aujourd'hui le comportement dépend à 100 % du `.env`, cause racine historique du Redis→500.

---

## 3. PRIORITÉ 2 — Sécurité

| # | Tâche | Gravité | Réf |
|---|-------|---------|-----|
| SEC-1 | Auto-vérification email basée sur `mail.mailer=log` : restreindre au strict `app()->environment('local','testing')` | Critique | `AuthController.php:70-72` |
| SEC-2 | Confirmer/sécuriser le chargement de `auth.php`/`admin.php`/`profile_share.php` et leurs middlewares (`role:admin`, `auth`) | Critique | `bootstrap/app.php` |
| SEC-3 | Ajouter du **rate limiting** sur le groupe authentifié et surtout sur les endpoints IA payants (`attempt.complete`) — risque de DoS économique | Élevé | `routes/web.php` |
| SEC-4 | **Supprimer `install.php` du webroot** après installation (ou Basic Auth) ; forcer `APP_ENV=production`/`APP_DEBUG=false` à l'install | Élevé | `public/install.php`, `.env.example` |
| SEC-5 | Corriger `EnsureSubscribed` : `hasRole('admin|super-admin')` → `hasAnyRole(['admin','super-admin'])` (le pipe n'est pas parsé) | Moyen | `Middleware/EnsureSubscribed.php:27` |
| SEC-6 | Retirer le `v-html` résiduel sur les labels de pagination | Moyen | `Admin/Subscriptions/Index.vue:171` |

**Points positifs confirmés :** aucun secret committé, clés IA chiffrées en base, upload CV robuste (double validation + magic bytes), IDOR correctement bloqués (`user_id` vérifié), RGPD complet, mass assignment protégé, token de partage CSPRNG 48 car. Bon niveau global.

---

## 4. PRIORITÉ 3 — Fonctionnel (conformité cahier des charges)

5 exigences sur 7 sont complètes. Écarts :

- **FONC-1 (bloquant)** : **émission des invitations absente**. L'infra mailing existe et la *réception* d'invitation (`/i/{token}`) fonctionne, mais **aucun code ne crée ni n'envoie** d'invitation (`TestInvitation::create()` = 0 occurrence, pas de route admin, pas de mailable d'invitation). Le dashboard conseiller est en lecture seule. → Créer le flux d'émission (saisie email + test → création invitation → envoi mail en queue, + import CSV).
- **FONC-2** : « exactement 15 métiers » non garanti — ajouter une validation post-IA dans `JobSuggestionService` (tronquer/relancer si ≠ 15).
- **FONC-3** : garde d'onboarding par contrôleur (403 brut) plutôt que middleware — ajouter `EnsureProfileComplete` qui **redirige** vers `/onboarding`.

---

## 5. PRIORITÉ 4 — Technique / performance

- **TECH-1** : Retirer le **hack CSS `!important`** (~87 lignes injectées dans `app.blade.php` après `@vite`) maintenant que le bon bundle est en prod ; basculer le thème dans `tailwind.config.js`. Sinon tout futur changement de design est invisible.
- **TECH-2** : Corriger les **N+1 du chemin chaud gamification** : `BadgeEvaluator` (1 requête/badge + counts répétés), `GamificationEngine::awardXp` (double requête), dénormaliser/cacher `User::totalXp()`.
- **TECH-3** : `SubscriptionController` charge toute la table puis compte en PHP → compter en SQL (`selectRaw`) + cache.
- **TECH-4** : Extraire la logique des contrôleurs « riches » en services (`ConseillerDashboardController` ~217 l., `TestEditorController::saveStructure` ~105 l.).
- **TECH-5** : Nettoyer le repo git : `git rm --cached` sur `_imports/`, `plugins/_wp_import/` (dont le dossier au nom cassé `{includes,admin,…`), `*.zip`, `ziCvafWC`, `.docx`/`.html` racine ; régénérer l'index git corrompu ; retirer `predis` (pas de Redis).
- **TECH-6** : `PluginManager::bootEnabledPlugins()` interroge la DB à chaque requête — mettre en cache la liste des plugins actifs.

---

## 6. PRIORITÉ 5 — QA / tests

La suite **n'a pas pu être exécutée dans le sandbox** (pas de PHP) — à relancer sur le poste/OVH : `composer install && php artisan test`. Couverture actuelle : Onboarding, Upload, RGPD = bons ; scoring de 5 plugins sur 11. **Trous majeurs** :

- **QA-1** : **Auth non testé** (register/login/reset/throttle) — première brique du parcours.
- **QA-2** : **Génération IA non testée** (synthèse + 15 métiers) — seul le *dispatch* du job l'est. C'est la promesse produit ; tester avec `Http::fake` et vérifier le nombre exact de métiers.
- **QA-3** : **Billing/Cashier** et **autorisation par rôle** (accès `/admin/*`) non testés.
- **QA-4** : 6 moteurs de scoring sans test (PraxiFlow, PraxiLink, PraxiSelf, PraxiSpeak, PraxiZen, Praxis360). ⚠️ **PraxiLink a une signature `score()` divergente** des autres → risque de 500 au scoring de ce plugin (à corriger + tester).
- **QA-5** : Aucun test end-to-end ni test front (Vitest absent).
- Bug fonctionnel à corriger : `diffInDays() === 1` (comparaison float/int) casse le calcul de streak dans `JourneyProgress` et 5 plugins.

---

## 7. PRIORITÉ 6 — Ergonomie / accessibilité

Top améliorations UX :

- **UX-1 (critique a11y)** : dans `AttemptPlay.vue`, les choix de réponse sont des `<div>` non focusables → **un utilisateur clavier/lecteur d'écran ne peut pas passer le test**. Convertir en vrais radios/checkboxes ; ajouter navigation arrière + sortie/pause + message sur échec d'enregistrement.
- **UX-2** : Créer des **pages d'erreur thématisées** (`errors/500,503,404,419.blade.php`) — un 500 affiche aujourd'hui l'écran Laravel brut. Remplacer le `<meta refresh 10s>` de l'attente IA par un polling Inertia.
- **UX-3** : Respecter `prefers-reduced-motion` (absent partout) ; corriger les contrastes sous WCAG AA (`--text-muted/--text-ghost`, placeholders).
- **UX-4** : Rendre la **landing et le header candidat responsives** (grilles inline fixes, pas de menu mobile).
- **UX-5** : Industrialiser le design system (composants atomiques), trier/hiérarchiser les 15 métiers par fit, intégrer `ShareProfileButton` (existant mais inutilisé) sur la page résultats, aligner la voix de marque (`AuthLayout` parle encore de « bilan de compétences »).

---

## 8. Ordre d'attaque recommandé

1. **Aujourd'hui** : 500-1, 500-2, 500-3, 500-4 (4 correctifs ciblés = l'essentiel des 500 en prod).
2. **Cette semaine** : 500-5 à 500-10 (IA + déploiement assaini) ; SEC-1, SEC-2.
3. **Sprint suivant** : FONC-1 (invitations), SEC-3/4, TECH-1/2, QA-1/2.
4. **Backlog** : reste technique, QA, UX.

> Note : un agent a rencontré un index git corrompu / fichiers tronqués dans sa copie montée (`AttemptController`, `storeCv`…). Les autres agents ont lu ces fichiers intégralement, donc c'était transitoire — mais un `git fsck` / clone propre est recommandé avant de retravailler ces fichiers.
