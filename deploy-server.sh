#!/bin/bash
# ================================================================
# PraxiQuest — Déploiement SERVEUR (à lancer en SSH sur OVH)
# Cible : ~/praxiquest (la SEULE app servie par praxiquest.decisionpro.fr)
# Usage : cd ~/praxiquest && bash deploy-server.sh
# Pré-requis : avoir lancé deploy-ovh.ps1 en local (push GitHub) avant.
# ================================================================
set -e
set -o pipefail   # un échec dans un pipe (ex: composer | tail) fait planter le script
GREEN="\033[32m"; YELLOW="\033[33m"; RED="\033[31m"; RESET="\033[0m"
ok() { echo -e "${GREEN}✓ $1${RESET}"; }
msg() { echo -e "${YELLOW}→ $1${RESET}"; }
err() { echo -e "${RED}✗ $1${RESET}"; exit 1; }

cd "$HOME/praxiquest" || { echo "✗ ~/praxiquest introuvable"; exit 1; }

# Résolution de composer (PATH non chargé en shell non-interactif)
COMPOSER_BIN="$(command -v composer || command -v composer2 || true)"
if [ -z "$COMPOSER_BIN" ] && [ -f "$HOME/composer.phar" ]; then
  COMPOSER_BIN="php $HOME/composer.phar"
fi
[ -z "$COMPOSER_BIN" ] && err "composer introuvable (PATH ou ~/composer.phar)"

# Le .env du serveur (bon DB_PASSWORD) est gitignoré : il ne sera PAS touché.

msg "Remise à l'état git des fichiers patchés à la main (corrigés dans le repo)..."
git checkout -- app/Http/Controllers/Candidate/OnboardingController.php \
                app/Core/Gamification/GamificationEngine.php \
                composer.json 2>/dev/null || true
ok "Fichiers patchés réinitialisés"

# composer.lock est désormais versionné (fix 500 Cashier : le lock local du
# serveur datait de Cashier ≤14 qui requête subscriptions.name, alors que la
# migration crée le schéma v15 avec la colonne `type`). Si un lock non suivi
# traîne encore sur le serveur, il bloquerait le pull → on le supprime.
if [ -f composer.lock ] && ! git ls-files --error-unmatch composer.lock >/dev/null 2>&1; then
    rm -f composer.lock
    ok "composer.lock obsolète (non suivi) supprimé"
fi

msg "Pull du code..."
# NE PAS faire 'rm -rf public/build/' : après un rm, un git pull en merge ne
# restaure QUE les fichiers modifiés dans le commit entrant → les chunks Vite
# inchangés (ex. _plugin-vue_export-helper-*.js) restaient supprimés → assets
# manquants (MIME text/html / 404) → page blanche. public/build est suivi par
# git ; on force juste sa cohérence avec le commit distant après le pull.
git pull origin main
git checkout HEAD -- public/build 2>/dev/null || true
ok "Code à jour"

msg "Composer install ($COMPOSER_BIN)..."
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction
ok "Dépendances PHP OK"

msg "Migrations (nouvelles tables/colonnes)..."
php artisan migrate --force --no-interaction
ok "Migrations OK"

msg "Seeders idempotents (référentiels)..."
php artisan db:seed --class=CareerPathsSeeder --force --no-interaction
ok "Référentiel des pistes métiers (PTP) OK"

# Orientation Express — instrument RIASEC (upsert par section/order, idempotent).
php artisan db:seed --class=DemoTestSeeder --force --no-interaction
ok "Test Orientation Express (RIASEC) OK"

# L'Étoffe du Bâtisseur — compétences entrepreneuriales (idempotent).
php artisan db:seed --class=EntrepreneurTestSeeder --force --no-interaction
ok "Test Compétences entrepreneuriales OK"

msg "Découverte et activation des nouveaux plugins..."
php artisan praxiquest:plugins:discover --sync
ok "Plugins découverts"

msg "Reconstruction des caches..."
mkdir -p storage/framework/views   # view:clear plante si ce dossier est absent
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Caches OK"

# Reset OPcache : l'OVH mutualisé (PHP-FPM) ne recompile pas les classes PHP
# modifiées au déploiement → les changements de contrôleurs/services ne prenaient
# effet qu'au bout d'un moment. On force le reset via une requête web (contexte FPM).
msg "Reset OPcache..."
printf '%s' '<?php if (function_exists("opcache_reset")) { opcache_reset(); } echo "ok";' > public/__opcache_reset.php
curl -fsS "https://praxiquest.fr/__opcache_reset.php" >/dev/null 2>&1 || true
curl -fsS "https://praxiquest.decisionpro.fr/__opcache_reset.php" >/dev/null 2>&1 || true
rm -f public/__opcache_reset.php
ok "OPcache reset"

msg "Permissions..."
chmod -R 775 storage bootstrap/cache
ok "Permissions OK"

# Sécurité : neutraliser l'installeur web après déploiement (cf. audit C-1).
# install.php peut DROP toutes les tables ; il ne doit jamais rester accessible
# sur une app déployée.
if [ -f public/install.php ]; then
    rm -f public/install.php && ok "install.php neutralisé (sécurité C-1)"
fi

echo ""
ok "Déploiement terminé → https://praxiquest.decisionpro.fr"
echo -e "${YELLOW}En cas de 500 :${RESET} grep \"production.ERROR\" storage/logs/laravel.log | tail -1"
