# Déploiement — index et procédure canonique

> **Ce fichier est la source de vérité pour déployer l'instance de production**
> (`praxiquest.decisionpro.fr` / `www.praxiquest.fr`). Dernière mise à jour : 2026-07-15.

## Procédure de déploiement production (la seule valide)

```powershell
# 1. En LOCAL (PowerShell)
.\deploy-ovh.ps1          # build Vite + git add (dont public/build forcé) + commit + push
```

```bash
# 2. Sur le SERVEUR (SSH decisiv@ssh.cluster121.hosting.ovh.net, mot de passe interactif)
cd ~/praxiquest && bash deploy-server.sh
# git pull → composer install → migrate → praxiquest:plugins:discover --sync → caches
```

```powershell
# 3. Vérification post-déploiement
curl -s -w "%{content_type}" https://praxiquest.decisionpro.fr/build/manifest.json
# doit renvoyer application/json — puis vérifier un marqueur du code dans un asset du manifest
```

## ⚠️ Pièges connus

- **Le docroot servi est `~/praxiquest/public`** (vérifié le 2026-06-30 par hash de manifest).
  `~/www` est un ANCIEN clone non servi : ne jamais y déployer. (`deploy-server.sh` cible
  `$HOME/praxiquest` depuis le commit `a91131a`.)
- La commande artisan est **`praxiquest:plugins:discover`** (pas `plugins:discover`).
- OVH mutualisé : cron limité à 1×/heure (`cron-scheduler.php`), aucune connexion sortante
  en SSH (tester les emails depuis le contexte web uniquement — Brevo API).
- Ne jamais déduire le schéma de base prod des fichiers de migration : vérifier en base
  (`Schema::hasColumn`) — cf. incident leads.deleted_at du 2026-07-04.

## Rôle de chaque document de ce dossier

| Document | Rôle | Statut |
|----------|------|--------|
| **README.md** (ce fichier) | Procédure canonique production | ✅ À jour |
| DEPLOY.md | Guide client final (installation zip + install.php) | Générique |
| DEPLOY-PERSO-OVH.md | Guide OVH Perso sans SSH (zip GitHub Actions) | Générique |
| DEPLOY-OVH.md | Guide OVH générique (plans, ~/www) | ⚠️ Chemins invalides pour NOTRE prod |
| DEBUG_OVH.md | Journal de debug 500 du 19/06 | Historique |
| DEBUG_500_RESOLUTION.md | Résolution 500 (cartographie ~/praxiquest vs ~/www) | Historique, chemins corrects |
| RESOLUTION-500-TESTS-2026-06-20.md | Résolution 500 /tests | Historique, chemins corrects |
| DEPLOY_LOG.md / DEPLOY_STATUS.md | Journaux de déploiement | Historique |
