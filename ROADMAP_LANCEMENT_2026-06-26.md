# Roadmap de lancement commercial — PraxiQuest

**Date :** 26 juin 2026
**Objectif visé :** lancement commercial (abonnements Stripe) avec **inscription libre** (n'importe qui peut créer un compte).
**Scénario le plus exigeant** → priorité absolue à la sécurité du parcours d'inscription et à la fiabilité de la facturation.

Ce document récapitule **ce qui a été implémenté côté code** dans cette session, puis liste **les actions manuelles restantes** (tes accès : OVH, DNS, dashboard Stripe, .env) avec leurs critères de validation.

---

## 1. Ce qui a été fait (code) ✅

### 1.1 Vérification d'email (le bloquant n°1)

Sans elle, n'importe qui pouvait s'inscrire avec une adresse bidon. Le flux complet est maintenant implémenté.

| Fichier | Changement |
|---|---|
| `app/Http/Controllers/Auth/EmailVerificationController.php` | **Nouveau** — pages notice / verify (lien signé) / resend, avec try/catch SMTP. |
| `app/Http/Middleware/EnsureEmailVerified.php` | **Nouveau** — gate de vérification avec **kill-switch** (config) + exemption des comptes staff. |
| `resources/js/Pages/Auth/VerifyEmail.vue` | **Nouveau** — page « Confirmez votre adresse » + bouton renvoyer le lien. |
| `routes/auth.php` | Routes `verification.notice` / `verification.verify` / `verification.send`. |
| `routes/web.php` | Middleware `verified` réactivé sur le groupe candidat **et** billing. |
| `bootstrap/app.php` | Alias `verified` surchargé par notre middleware (kill-switch + staff). |
| `app/Http/Middleware/HandleInertiaRequests.php` | Partage `auth.email_verified` + flash `status`/`warning` au front. |
| `config/praxiquest.php` | Bloc `security.require_email_verification` (kill-switch). |

**Parcours résultant :** inscription → page « vérifiez votre email » → clic sur le lien reçu → onboarding (statut / ancienneté / CV) → tests.

> ⚠️ **Kill-switch** : si le SMTP de prod tombe, mettre `REQUIRE_EMAIL_VERIFICATION=false` dans `.env` pour débloquer temporairement les inscriptions, **sans redéployer de code**. Les comptes admin/super-admin sont exemptés automatiquement.

### 1.2 Anti-abus inscription

| Fichier | Changement |
|---|---|
| `app/Http/Controllers/Auth/AuthController.php` | **Honeypot** anti-bot (`website`) : abandon silencieux si rempli. |
| `resources/js/Pages/Auth/Register.vue` | Champ honeypot invisible + accessible uniquement aux bots. |
| `config/praxiquest.php` | Scaffold config **Turnstile** (captcha) piloté par env, inactif par défaut. |

Le rate-limiting existait déjà et reste en place : `register` 10/h, `login` 5/min, reset 3/10min, endpoints IA (Oracle 20/min, Grimoire 3/min). Le honeypot s'ajoute par-dessus, sans configuration.

> Le captcha **Turnstile** est seulement *scaffoldé* (config). Le widget front n'est pas branché — voir §2.6 pour l'activer quand tu veux durcir davantage. Le honeypot suffit pour démarrer.

### 1.3 Conformité légale

| Fichier | Changement |
|---|---|
| `resources/js/Pages/Public/Mentions.vue` | **Nouveau** — Mentions légales (obligatoire, art. 6 LCEN) : éditeur, directeur de publication, hébergeur OVH. |
| `resources/js/Pages/Public/Contact.vue` | **Nouveau** — page Contact / support. |
| `app/Http/Controllers/LegalController.php` | Méthodes `mentions()` + `contact()`. |
| `routes/web.php` | Routes `/mentions-legales` et `/contact`. |
| `resources/js/Pages/Public/Landing.vue` | Liens « Mentions légales » + « Contact » ajoutés au footer. |
| `config/praxiquest.php` | Blocs `contact` + `legal` (éditeur / hébergeur, pilotés par env). |

- **Acceptation des CGU à l'inscription** : déjà en place (case obligatoire + stockage `terms_accepted_at` / `terms_version`). ✅
- **CGU** et **Politique de confidentialité** : déjà rédigées et conformes (RGPD, disclaimers IA, sous-traitants Stripe/IA, cookies). ✅
- **Bandeau cookies** : **non ajouté volontairement**. La plateforme n'utilise que des cookies strictement nécessaires (session, sécurité) → aucun consentement requis par la CNIL, seulement une information (déjà présente dans les CGU/Confidentialité). À ajouter **uniquement** si tu intègres plus tard de l'analytics ou du marketing tiers (voir §2.7).

### 1.4 Durcissement Stripe

| Fichier | Changement |
|---|---|
| `app/Http/Controllers/BillingController.php` | Garde-fou si Price ID Stripe vide (évite une 500 « No such price ») + catch générique sur le checkout (message propre au lieu d'une 500). |

Le reste du `BillingController` était déjà solide : checkout avec essai 14 j, swap de plan si déjà abonné, gestion `IncompletePayment`, portail client, annulation/réactivation, liste des factures. Le **webhook** est géré nativement par Cashier (`routes/web.php` → `cashier.webhook`), il ne reste qu'à le configurer côté Stripe (§2.3).

---

## 2. Ce qu'il reste à faire (actions manuelles) ⏳

> Ces étapes nécessitent **tes** accès (OVH, DNS, dashboard Stripe). Elles ne peuvent pas être faites depuis le code.

### 2.1 Variables d'environnement (`.env` sur OVH)

```dotenv
# Vérification email (activer APRÈS avoir validé l'envoi SMTP — §2.2)
REQUIRE_EMAIL_VERIFICATION=true

# Email transactionnel
MAIL_MAILER=smtp
MAIL_HOST=ssl0.ovh.net
MAIL_PORT=465
MAIL_USERNAME=contact@decisionpro.fr
MAIL_PASSWORD=********
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=contact@decisionpro.fr
MAIL_FROM_NAME="PraxiQuest"

# Stripe (clés en mode LIVE une fois les tests passés)
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
STRIPE_PRICE_STARTER_MONTHLY=price_xxx
STRIPE_PRICE_STARTER_YEARLY=price_xxx
STRIPE_PRICE_PRO_MONTHLY=price_xxx
STRIPE_PRICE_PRO_YEARLY=price_xxx
STRIPE_PRICE_ENTERPRISE_MONTHLY=price_xxx
STRIPE_PRICE_ENTERPRISE_YEARLY=price_xxx

# Contact + mentions légales
PRAXIQUEST_CONTACT_EMAIL=contact@decisionpro.fr
LEGAL_EDITOR_SIRET=          # ⚠️ À COMPLÉTER (obligatoire)
LEGAL_EDITOR_ADDRESS=        # ⚠️ À COMPLÉTER (adresse de l'éditeur)
LEGAL_PUBLISHER="Alexandre Fradin"
```

**Done quand :** `php artisan config:clear` puis chaque variable est lue (tester un envoi + une page /mentions-legales correcte).

### 2.2 Email transactionnel + délivrabilité DNS

L'email de vérification, le reset de mot de passe, les invitations et les reçus Stripe **doivent arriver** (sinon ils finissent en spam ou ne partent pas).

- [ ] Configurer le SMTP (OVH ou un provider transactionnel type Brevo/Postmark/Mailgun — recommandé pour la délivrabilité).
- [ ] **SPF** : enregistrer le domaine d'envoi dans le TXT SPF de `decisionpro.fr`.
- [ ] **DKIM** : activer la signature DKIM (clé fournie par OVH/le provider).
- [ ] **DMARC** : ajouter un enregistrement `_dmarc` (au moins `p=none` pour démarrer et monitorer).

**Done quand :** un compte de test reçoit le mail de vérification en boîte de réception (pas spam), et [mail-tester.com](https://www.mail-tester.com) donne un score ≥ 8/10.

### 2.3 Stripe — dashboard

- [ ] Créer **3 produits** (Starter / Pro / Enterprise), chacun avec un prix **mensuel** et un prix **annuel** → copier les 6 `price_…` dans `.env`.
- [ ] Créer le **webhook** : endpoint `https://decisionpro.fr/stripe/webhook`, événements :
  `customer.subscription.created`, `customer.subscription.updated`, `customer.subscription.deleted`,
  `invoice.payment_succeeded`, `invoice.payment_failed`, `customer.updated`, `customer.deleted`
  → copier le **signing secret** (`whsec_…`) dans `STRIPE_WEBHOOK_SECRET`.
- [ ] Activer le **Customer Portal** (Settings → Billing → Customer portal) — utilisé par le bouton « gérer mon abonnement ».

**Done quand :** un abonnement de test en mode LIVE passe : checkout → actif → annulation → réactivation, et le statut se met à jour en base après chaque webhook.

### 2.4 Build + déploiement

Les 3 nouvelles pages Vue (`VerifyEmail`, `Mentions`, `Contact`) **doivent être buildées**.

- [ ] Build Vite **sur Windows** (`deploy-ovh.ps1`), `public/build` commité (`git add -f`).
- [ ] Déployer (GitHub → OVH via `deploy-server.sh`).
- [ ] Sur OVH : `php artisan config:clear && php artisan route:clear && php artisan view:clear`.
- [ ] Vérifier l'absence de fichiers tronqués / null-bytes après push (cf. tes notes de déploiement).

**Done quand :** `/email/verify`, `/mentions-legales`, `/contact` répondent en 200.

### 2.5 Tests de bout en bout (à faire par toi, pas de PHP en sandbox ici)

- [ ] Inscription d'un nouveau compte → mail reçu → clic → onboarding → 1er test → synthèse IA + métiers → Grimoire / Oracle.
- [ ] Renvoyer le lien de vérification (bouton) fonctionne.
- [ ] Reset de mot de passe fonctionne.
- [ ] Parcours mobile complet (responsive).
- [ ] Checkout Stripe + portail + annulation.

### 2.6 (Optionnel) Activer le captcha Turnstile

Quand tu veux durcir au-delà du honeypot :
1. Créer un site Turnstile (Cloudflare) → récupérer site key + secret.
2. `.env` : `TURNSTILE_ENABLED=true`, `TURNSTILE_SITE_KEY=…`, `TURNSTILE_SECRET_KEY=…`.
3. Brancher le widget dans `Register.vue` (script Turnstile + champ `cf-turnstile-response`) et vérifier le token côté `AuthController::register()`. *(Ce câblage front/serveur reste à écrire — me le demander le moment venu.)*

### 2.7 (Optionnel) Identité de marque

- [ ] Logo + favicon définitifs (`PRAXIQUEST_LOGO_URL`), cohérence des couleurs sur landing + emails.
- [ ] Vérifier le rendu des emails transactionnels (header/logo).

---

## 3. Ordre de déploiement recommandé

1. **DNS email** (SPF/DKIM/DMARC) — à faire en premier (propagation lente).
2. **`.env`** mail + contact + SIRET/adresse, **`REQUIRE_EMAIL_VERIFICATION=false`** pour l'instant.
3. **Build + deploy** + `config:clear`.
4. **Tester l'envoi d'email** (compte de test). Une fois OK → passer **`REQUIRE_EMAIL_VERIFICATION=true`**.
5. **Stripe** : produits + prix + webhook + portail, en **mode test** d'abord.
6. Tests bout-en-bout en mode test → bascule en **mode LIVE**.
7. Compléter logo / marque, puis ouverture publique.

---

## 4. Récapitulatif des bloquants

| Item | État |
|---|---|
| Vérification email (flux complet) | ✅ Codé — reste à activer après SMTP OK |
| Anti-abus (honeypot + throttle) | ✅ Codé et actif |
| Acceptation CGU à l'inscription | ✅ Déjà en place |
| CGU / Confidentialité | ✅ Déjà conformes |
| Mentions légales | ✅ Codé — reste SIRET + adresse à renseigner |
| Page contact | ✅ Codé |
| Durcissement checkout Stripe | ✅ Codé |
| SMTP + DNS délivrabilité | ⏳ Action OVH/DNS |
| Stripe dashboard (prix + webhook + portail) | ⏳ Action dashboard |
| Build + deploy + tests | ⏳ Action toi |
| Logo / identité de marque | ⏳ Optionnel pour ouvrir |

**Estimation :** ~1 à 2 jours de config (DNS, Stripe, .env, tests) côté toi pour passer du code livré ici à l'ouverture commerciale.
