#!/usr/bin/env bash
# ==========================================
# PraxiQuest — Setup Mac/Linux
# ==========================================
set -e

echo ""
echo "========================================"
echo " PraxiQuest — Installation locale"
echo "========================================"
echo ""

# Verifie outils
command -v php >/dev/null 2>&1 || { echo "✗ PHP introuvable. Installe PHP 8.2+"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "✗ Composer introuvable. https://getcomposer.org"; exit 1; }
command -v node >/dev/null 2>&1 || { echo "✗ Node introuvable. Installe Node 20+"; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "✗ npm introuvable"; exit 1; }

echo "✓ PHP $(php -r 'echo PHP_VERSION;')"
echo "✓ Composer $(composer --version | head -1)"
echo "✓ Node $(node -v)"
echo ""

step="${1:-all}"

if [ "$step" = "all" ] || [ "$step" = "deps" ]; then
    echo "--- composer install ---"
    composer install --no-interaction --prefer-dist
    echo ""
    echo "--- npm install ---"
    npm install
fi

if [ "$step" = "all" ] || [ "$step" = "env" ] || [ "$step" = "deps" ]; then
    if [ ! -f .env ]; then
        cp .env.example .env
        echo "✓ .env créé"
    else
        echo "ℹ .env existe déjà, conservé"
    fi
    php artisan key:generate --force
fi

if [ "$step" = "deps" ]; then
    echo ""
    echo "========================================"
    echo " Configure ta base dans .env (DB_DATABASE)"
    echo " puis relance : ./setup.sh finish"
    echo "========================================"
    exit 0
fi

if [ "$step" = "all" ] || [ "$step" = "finish" ] || [ "$step" = "db" ]; then
    echo ""
    echo "--- Migrations + seed + plugins ---"
    php artisan storage:link 2>/dev/null || true
    php artisan migrate --force
    php artisan db:seed --force
    php artisan praxiquest:plugins:discover --sync

    for slug in praximet praxivaleurs praxicare praxiemo praximum; do
        echo "Activation $slug..."
        php artisan praxiquest:plugins:activate "$slug" || echo "  (déjà activé)"
    done
fi

if [ "$step" = "all" ] || [ "$step" = "build" ]; then
    echo ""
    echo "--- Build assets ---"
    npm run build
fi

echo ""
echo "========================================"
echo " ✓ Installation terminée"
echo " Démarrer : php artisan serve"
echo " Queue    : php artisan queue:work"
echo " Login    : admin@praxiquest.local / changeme123"
echo "========================================"
