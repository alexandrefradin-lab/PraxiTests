Set-Location "C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests"

Write-Host "=== npm install ===" -ForegroundColor Cyan
npm install
if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR npm install" -ForegroundColor Red; Read-Host; exit }

Write-Host "=== npm run build ===" -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR npm run build" -ForegroundColor Red; Read-Host; exit }

Write-Host "=== Nouveaux fichiers dans public/build ===" -ForegroundColor Yellow
git status public/build --short

Write-Host "=== git add -f public/build ===" -ForegroundColor Cyan
git add -f public/build

Write-Host "=== Fichiers staged ===" -ForegroundColor Yellow
git diff --cached --stat

Write-Host "=== git commit + push ===" -ForegroundColor Cyan
git commit -m "feat: landing page aventure — voyage interieur, terra incognita"
git push

Write-Host "=== DONE — faites git pull en SSH ===" -ForegroundColor Green
Read-Host "Appuyez sur Entree pour fermer"
