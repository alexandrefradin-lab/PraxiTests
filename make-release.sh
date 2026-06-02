#!/usr/bin/env bash
# ==========================================
# PraxiQuest — Build distribuable (zip prêt-à-déployer)
# Nécessite : PHP 8.2+, Composer, Node 20+ INSTALLÉS
# ==========================================
set -e

VERSION="1.0.0-alpha"
OUT_DIR="dist"
ZIP_NAME="praxiquest-${VERSION}.zip"

echo ""
echo "========================================"
echo " PraxiQuest — Build release v${VERSION}"
echo "========================================"
echo ""

# 1. Nettoyage
rm -rf "$OUT_DIR" "$ZIP_NAME"
mkdir -p "$OUT_DIR"

echo "[1/5] composer install --no-dev"
composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "[2/5] npm install"
npm install --silent

echo ""
echo "[3/5] npm run build (assets prod)"
npm run build

echo ""
echo "[4/5] Copie des fichiers (exclusion dev)"
rsync -a --exclude='.env' --exclude='.installed' \
    app/ bootstrap/ config/ database/ public/ \
    resources/ routes/ storage/ vendor/ plugins/ docs/ \
    "$OUT_DIR/" 2>/dev/null || {
    # fallback sans rsync (Mac sans options)
    for d in app bootstrap config database public resources routes storage vendor plugins docs; do
        cp -R "$d" "$OUT_DIR/"
    done
}
cp artisan composer.json package.json .env.example README.md INSTALL.md "$OUT_DIR/"
[ -f composer.lock ] && cp composer.lock "$OUT_DIR/"
[ -f DEPLOY.md ]     && cp DEPLOY.md     "$OUT_DIR/"
[ -f LICENSE ]       && cp LICENSE       "$OUT_DIR/"

# Recopie séparée des dossiers cachés (.gitignore dans storage/)
for d in storage bootstrap; do
    find "$d" -name '.gitignore' -exec cp --parents {} "$OUT_DIR/" \; 2>/dev/null || true
done

# Cleanup avant zip
rm -f "$OUT_DIR/storage/app/.installed" "$OUT_DIR/.env"

echo ""
echo "[5/5] Création du zip $ZIP_NAME"
( cd "$OUT_DIR" && zip -qr "../$ZIP_NAME" . )

# Restaurer deps dev
echo ""
echo "Restauration des dépendances dev..."
composer install --no-interaction --quiet

SIZE=$(du -h "$ZIP_NAME" | cut -f1)
echo ""
echo "========================================"
echo " ✓ Build OK : $ZIP_NAME ($SIZE)"
echo " Distribuer : envoyer le zip au client"
echo " Le client décompresse + ouvre /install.php"
echo "========================================"
