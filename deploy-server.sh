#!/bin/bash
# ================================================================
# PraxiQuest — Déploiement SERVEUR (à lancer en SSH sur OVH)
# Cible : ~/praxiquest (la SEULE app servie par praxiquest.decisionpro.fr)
# Usage : cd ~/praxiquest && bash deploy-server.sh
# Pré-requis : avoir lancé deploy-ovh.ps1 en local (push GitHub) avant.
# ================================================================
set -e
GREEN="\033[32m"; YELLOW="\033[33m"; RESET="\033[0m"
ok() { echo -e "${GREEN}✓ $1${RESET}"; }
msg() { echo -e "${YELLOW}→ $1${RESET}"; }

cd "$HOME/praxiquest" || { echo "✗ ~/praxiquest introuvable"; exit 1; }

# Le .env du serveur (bon DB_PASSWORD) est gitignoré : il ne sera PAS touché.

msg "Remise à l'état git des 2 fichiers patchés à la main (corrigés dans le repo)..."
git checkout -- app/Http/Controllers/Candidate/OnboardingController.php \
                app/Core/Gamification/GamificationEngine.php 2>/dev/null || true
ok "Fichiers patchés réinitialisés"

msg "Pull du code..."
rm -rf public/build/
git pull origin main
ok "Code à jour"

msg "Composer install (composer.json a changé)..."
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -4
ok "Dépendances PHP OK"

msg "Migrations (nouvelles tables/colonnes)..."
php artisan migrate --force --no-interaction
ok "Migrations OK"

msg "Reconstruction des caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Caches OK"

msg "Permissions..."
chmod -R 775 storage bootstrap/cache
ok "Permissions OK"

echo ""
ok "Déploiement terminé → https://praxiquest.decisionpro.fr"
echo -e "${YELLOW}En cas de 500 :${RESET} grep \"production.ERROR\" storage/logs/laravel.log | tail -1"
