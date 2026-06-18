#!/bin/bash
# ================================================================
# PraxiQuest — Script de déploiement SSH (OVH Pro cluster121)
# Usage : bash deploy.sh [first|update]
# ================================================================

set -e
BOLD="\033[1m"
GREEN="\033[32m"
RED="\033[31m"
YELLOW="\033[33m"
RESET="\033[0m"

ok()  { echo -e "${GREEN}✓${RESET} $1"; }
err() { echo -e "${RED}✗${RESET} $1"; exit 1; }
msg() { echo -e "${BOLD}→ $1${RESET}"; }

MODE=${1:-update}
APP_DIR="$HOME/praxiquest"

# ── Première installation ──────────────────────────────────────
if [ "$MODE" = "first" ]; then
  msg "Clonage du dépôt..."
  cd "$HOME"
  git clone https://github.com/alexandrefradin-lab/PraxiTests.git praxiquest || err "git clone échoué"
  ok "Dépôt cloné dans ~/praxiquest"

  msg "Création du .env..."
  cp "$APP_DIR/.env.example" "$APP_DIR/.env"
  echo ""
  echo -e "${YELLOW}⚠  Édite maintenant le .env :${RESET}"
  echo "   nano $APP_DIR/.env"
  echo ""
  echo "   Variables à renseigner :"
  echo "   APP_URL=https://praxiquest.decisionpro.fr"
  echo "   DB_HOST=xxxxx.mysql.db"
  echo "   DB_DATABASE=nom_de_ta_base"
  echo "   DB_USERNAME=utilisateur_db"
  echo "   DB_PASSWORD=mot_de_passe_db"
  echo "   PRAXIQUEST_ADMIN_EMAIL=ton@email.com"
  echo "   PRAXIQUEST_ADMIN_PASSWORD=motdepasse_admin"
  echo ""
  read -p "Appuie sur Entrée quand le .env est prêt..."
fi

# ── Étapes communes (first + update) ──────────────────────────
cd "$APP_DIR" || err "Dossier $APP_DIR introuvable"

msg "Pull Git..."
git pull origin main
ok "Code à jour"

msg "Composer install (production)..."
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -5
ok "Dépendances installées"

msg "Génération de la clé APP_KEY (si absente)..."
php artisan key:generate --no-interaction 2>/dev/null && ok "Clé générée" || ok "Clé déjà présente"

msg "Migrations..."
php artisan migrate --force --no-interaction
ok "Migrations OK"

if [ "$MODE" = "first" ]; then
  msg "Seed (données initiales)..."
  php artisan db:seed --force --no-interaction
  ok "Seed OK"

  msg "Découverte et activation des plugins..."
  php artisan praxiquest:plugins:discover --sync
  for slug in praximet praxivaleurs praxicare praxiemo praximum; do
    php artisan praxiquest:plugins:activate "$slug" && ok "Plugin $slug activé" || echo "  ⚠ Plugin $slug ignoré"
  done
fi

msg "Caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Caches générés"

msg "Lien storage..."
php artisan storage:link 2>/dev/null && ok "Storage lié" || ok "Storage déjà lié"

msg "Permissions storage/ et bootstrap/cache..."
chmod -R 775 storage bootstrap/cache
ok "Permissions OK"

echo ""
echo -e "${GREEN}${BOLD}✓ Déploiement terminé !${RESET}"
echo -e "  → https://praxiquest.decisionpro.fr"
