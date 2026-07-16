# Checklist de lancement commercial — www.praxiquest.fr

> Actions à réaliser par Alexandre (accès comptes requis). Mise à jour : 2026-07-15.
> Le code correspondant est déjà en place — chaque section indique quoi activer.

## 1. Stripe (bloquant pour vendre)

- [ ] Créer les **3 produits × 2 prix** (mensuel/annuel) dans le dashboard Stripe (mode TEST d'abord)
- [ ] Renseigner les IDs de prix dans la config (`config/plans.php` / `.env`)
- [ ] Créer le webhook : `https://www.praxiquest.fr/stripe/webhook`
      → copier le secret dans `.env` : `STRIPE_WEBHOOK_SECRET=whsec_...` (cf. TODO SEC-C3 dans `bootstrap/app.php`)
- [ ] Activer le **Customer Portal** Stripe (gestion d'abonnement self-service)
- [ ] Tester le tunnel complet en mode TEST (checkout → webhook → abonnement visible)
- [ ] Basculer les clés en **LIVE** dans le `.env` prod + `php artisan config:cache`

## 2. Rotations de sécurité (secrets exposés en clair pendant le debug)

- [ ] **Mot de passe de la boîte contact@praxiquest.fr** (webmail.mail.ovh.net / manager OVH)
- [ ] **Clé API Brevo `praxiquest-prod`** : régénérer dans Brevo → SMTP & API,
      puis mettre à jour `BREVO_API_KEY` dans le `.env` prod + `php artisan config:cache`
- [ ] Vérifier après rotation : bouton « Relancer » une invitation → logs Brevo « Délivré »

## 3. Mentions légales (art. 6 LCEN — bloquant juridiquement)

- [ ] `.env` prod : `LEGAL_EDITOR_SIRET=...` et `LEGAL_EDITOR_ADDRESS=...`
      (placeholders vides dans `config/praxiquest.php` → la page légale est incomplète sans ça)
- [ ] `php artisan config:cache` après modification

## 4. Domaine canonique (SEO + cookies)

- [ ] Vérifier `APP_URL=https://www.praxiquest.fr` dans le `.env` prod
      (pilote les liens absolus des emails d'invitation/vérification)
- [ ] Activer la redirection 301 : `CANONICAL_REDIRECT_ENABLED=true` dans le `.env` prod
      (+ `CANONICAL_HOST=www.praxiquest.fr` si différent du défaut) + `php artisan config:cache`
- [ ] Tester : `curl -sI https://praxiquest.decisionpro.fr/` → doit renvoyer
      `301` + `Location: https://www.praxiquest.fr/`
- [ ] Vérifier que l'URL du webhook Stripe (§1) pointe bien vers le domaine canonique

## 5. Vérifications finales

- [ ] `curl -s -w "%{content_type}" https://www.praxiquest.fr/build/manifest.json` → `application/json`
- [ ] Parcours candidat complet en prod (invitation → inscription → test → restitution IA)
- [ ] Cron OVH actif (`cron-scheduler.php`, 1×/heure) : vérifier qu'un job queued part dans l'heure
- [ ] Optionnel : ajuster `AI_ORACLE_CHAT_TIMEOUT` (défaut 30 s) si l'Oracle coupe des réponses longues
