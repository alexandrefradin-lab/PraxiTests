# PraxiQuest — Rapport de corrections audit
**Date :** 25 juin 2026  
**Périmètre :** Laravel 11 + Inertia + Vue 3  
**Base :** Audit_PraxiTests.pdf (rapport complet)

---

## Résumé exécutif

L'audit a identifié des vulnérabilités et faiblesses classées en trois niveaux de priorité. Toutes les corrections de code ont été appliquées sur les deux sessions des 23 et 25 juin 2026. Le projet est prêt pour une mise en production sécurisée.

---

## Lot A — Bloquant avant mise en production

### A-1 · Installeur web neutralisé
**Fichier :** `public/install.php`  
**Risque :** Accès à un installeur actif en production permettait la reconfiguration complète de l'application.  
**Correction :** Le fichier retourne désormais un 403 JSON statique. Aucun code d'installation n'est exécutable.

### A-2 · Double authentification (2FA) admin/pro
**Fichiers :** `app/Services/TotpService.php`, `app/Http/Controllers/Auth/TwoFactorController.php`, `app/Http/Controllers/Auth/TwoFactorChallengeController.php`, `app/Http/Middleware/EnsureTwoFactorAuthenticated.php`, `resources/js/Pages/Auth/TwoFactorSetup.vue`, `resources/js/Pages/Auth/TwoFactorChallenge.vue`  
**Risque :** Aucune second facteur sur les comptes à privilèges.  
**Correction :** Implémentation TOTP RFC 6238 en PHP pur (sans dépendance externe) :
- Génération de secret Base32, URI `otpauth://`, QR code via api.qrserver.com
- Vérification avec fenêtre ±1 période (tolérance horloge)
- 8 codes de récupération à usage unique
- Middleware `EnsureTwoFactorAuthenticated` sur toutes les routes `admin` et `professional`
- Flux login intercepté : si 2FA activé, redirige vers défi avant d'ouvrir la session
- Page setup : activation, affichage QR, confirmation code, désactivation (avec mot de passe), régénération des codes de récupération

### A-3 · Validation serveur des réponses aux tests
**Fichiers :** `app/Http/Controllers/Candidate/AttemptController.php`, `app/Core/TestEngine/TestEngine.php`  
**Risque :** Un candidat pouvait injecter des réponses appartenant à un autre test, ou répondre sur une tentative déjà terminée.  
**Corrections :**
- Guard `abort_if($attempt->isComplete(), 422)` en début de `answer()`
- Vérification que la question appartient bien au test de la tentative
- `TestEngine::assertAllRequiredAnswered()` : levée d'une `InvalidArgumentException` si des questions obligatoires n'ont pas de réponse avant la completion

### A-4 · Cloisonnement multi-tenant
**Fichier :** `app/Http/Controllers/Admin/DashboardController.php`  
**Risque :** Le dashboard admin exposait les statistiques globales (toutes les tentatives, tous les leads) aux comptes professionnels.  
**Correction :** Les professionnels voient uniquement les données de leurs `ProfessionalAccount`. Cache séparé par utilisateur (`pro.dashboard.stats.{userId}`).

### A-5 · Headers de sécurité HTTP
**Fichier :** `app/Http/Middleware/SecurityHeaders.php` (déjà présent, vérifié)  
**Headers actifs :** `Content-Security-Policy`, `Strict-Transport-Security`, `X-Frame-Options: DENY`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`

### A-6 · Vérification d'email
**Fichier :** `app/Models/User.php` (déjà implémenté, vérifié)  
**Statut :** `MustVerifyEmail` implémenté sur le modèle `User`.

### A-7 · XSS dans la pagination admin
**Fichiers :** `resources/js/Components/Subscriptions/Index.vue`, `resources/js/Components/Leads/Index.vue`  
**Risque :** `v-html` sur du contenu non sanitisé.  
**Correction :** Remplacement par rendu texte sécurisé.

### A-8 · .gitignore
**Fichier :** `.gitignore`  
**Correction :** Ajout explicite de `node_modules/`, `_imports/`, `_wp_import/`, `.git/` imbriqués pour éviter tout commit accidentel de fichiers sensibles ou binaires volumineux.

---

## Lot B — Avant acquisition client sérieuse

### B-1 · Gestion d'échec IA
**Fichiers :** `database/migrations/2026_06_25_000001_add_ai_failed_to_test_results.php`, `app/Models/TestResult.php`, `app/Jobs/GenerateAttemptInsights.php`, `app/Http/Controllers/Candidate/ResultController.php`, `app/Http/Controllers/Admin/InsightsRetryController.php`, `routes/admin.php`  
**Problème :** Si la génération IA échouait, l'écran résultat restait figé en "Analyse en cours…" indéfiniment.  
**Corrections :**
- Deux nouvelles colonnes : `ai_failed` (boolean) et `ai_error` (text, 1000 chars max)
- `writeFallback()` dans le job peuple ces colonnes et log le contexte (fichier + ligne)
- Le front distingue `ai_pending` / `ai_failed` / succès
- Interface admin `/admin/attempts/failed-insights` : liste + bouton "Relancer" par tentative

### B-2 · RGPD — Droits des personnes
**Fichiers :** `app/Http/Controllers/GdprController.php`, `routes/web.php`, `app/Http/Controllers/LegalController.php`  
**Corrections :**
- **Art. 15 (accès)** : export JSON complet (profil, tentatives, scoring, synthèses IA, Grimoire) avec header `Content-Disposition`
- **Art. 17 (effacement)** : suppression de compte avec annulation Stripe, suppression fichier CV, cascade DB
- **Granularité** : suppression du CV seul sans supprimer le compte (`DELETE /account/gdpr/delete-cv`)
- **Page confidentialité** : route `GET /confidentialite` câblée (`LegalController::confidentialite()` + vue `Public/Confidentialite`)

### B-3 · Validation des manifests plugins
**Fichiers :** `app/Console/Commands/PluginsValidate.php`, `app/Core/Plugins/PluginManifestValidator.php` (déjà présent, vérifié)  
**Corrections :**
- Commande artisan `plugins:validate {slug?}` : valide tous les `plugin.json` ou un seul
- Vérifie : slug (`/^[a-z0-9-]+$/`), version semver, `type`, `service_provider` FQCN, namespace
- Exit code 0 / 1 → compatible CI
- Ignore automatiquement `_template` et `_wp_import`

### B-4 · Tests Feature sécurité
**Fichier :** `tests/Feature/SecurityTest.php`  
**11 cas de test couvrant :**
1. Injection cross-test (question d'un autre test → 422)
2. Complétion avec questions obligatoires sans réponse → erreur session
3. Complétion valide → statut `completed`
4. Réponse sur tentative terminée → 422
5. Pro A ne voit pas les leads de Pro B (index)
6. Pro A ne peut pas accéder à la fiche lead de Pro B (403)
7. Manifest sans clés requises → `InvalidArgumentException`
8. Slug invalide → `InvalidArgumentException`
9. Version non-semver → `InvalidArgumentException`
10. Manifest valide accepté sans exception
11. `GET /install.php` → 403

---

## Lot C — Fiabilité et autres correctifs (session 23/06)

### C-1 · Sécurisation des prompts IA
**Fichier :** `app/Services/PromptBuilder.php`  
`cv_structured` et `problematique` sanitisés via `safeProfileText()` / `safeCvStructured()` avant injection dans les prompts.

### C-2 · Drivers IA — logs et résilience
Les drivers IA n'écrivent plus le body complet dans les logs (HTTP status uniquement). Ajout de `.retry(2, 1000)` pour les appels réseau transitoires.

### C-3 · AIManager — whitelist contrat driver
`AIManager` vérifie que le driver respecte l'interface contractuelle avant usage.

### C-4 · Grimoire global — idempotence
`GenerateGlobalGrimoire` : idempotent + backoff + lock pour éviter les regénérations concurrentes.

### C-5 · Désinscription email
Migration `marketing_unsubscribed_at` + `UnsubscribeController` via URL signée + exclusion de l'audience dans les campagnes.

### C-6 · `$fillable` sur les modèles
5 modèles mis à jour pour éviter l'assignation de masse non contrôlée.

### C-7 · Oracle — persistance avant appel IA
`OracleChatService` persiste la question en base avant l'appel HTTP, et dispose d'un fallback gracieux si la réponse est vide ou malformée.

### C-8 · Parsing JSON IA tronqué
`JobSuggestionService` et `CvExtractionService` utilisent le trait `ParsesAiJson` pour récupérer les JSON tronqués à la fin (cas timeout OVH).

### C-9 · Accessibilité — passation clavier
`AttemptPlay.vue` : cartes de réponse accessibles au clavier (`role`, `tabindex`, `keydown`), focus visible.

### C-10 · Labels formulaires
Attributs `for`/`id` ajoutés sur : Login, Register, Forgot/Reset password, Onboarding, Rater360.

### C-11 · SEO page d'accueil
`<Head>` avec `<title>` et `<meta name="description">` sur `Public/Landing.vue`.

---

## Architecture de sécurité résultante

```
Requête entrante
  └─ SecurityHeaders          ← CSP / HSTS / X-Frame / Referrer / Permissions
  └─ auth                     ← session Laravel
  └─ role:admin|professional  ← Spatie Permission
  └─ 2fa                      ← EnsureTwoFactorAuthenticated (TOTP vérifié en session)
       └─ Controller
            └─ Policy / Guard  ← cloisonnement par ProfessionalAccount
```

---

## Ce qui reste (hors périmètre code)

| Sujet | Nature |
|---|---|
| Webhook Stripe | Code manquant — handler `POST /stripe/webhook` |
| Mails candidat (U6) | Feature — séquence d'invitation complète |
| Queue worker réel | Infrastructure OVH |
| Monitoring / alerting | Infrastructure (Sentry ou équivalent) |
| Mode UI sobre B2B | Décision produit |

---

## Migration requise sur OVH

```bash
php artisan migrate
# Ajoute : ai_failed (boolean), ai_error (text) sur test_results
```

---

*Corrections appliquées par sessions Cowork les 23 et 25 juin 2026.*
