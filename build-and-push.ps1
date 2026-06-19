Set-Location "C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests"

Write-Host "=== Build Vite (theme AC parchmin) ===" -ForegroundColor Cyan
npm run build

Write-Host "=== Git force-add public/build (bypass .gitignore) ===" -ForegroundColor Cyan
git add -f public/build

Write-Host "=== Commit + Push ===" -ForegroundColor Cyan
git commit -m "chore: rebuild Vite assets theme AC parchmin"
git push

Write-Host "=== Done! Maintenant faites git pull en SSH ===" -ForegroundColor Green
Read-Host "Appuyez sur Entree pour fermer"
