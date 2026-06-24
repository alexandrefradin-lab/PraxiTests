# ================================================================
# PraxiQuest — Déploiement LOCAL (build + commit + push)
# À lancer sur Windows (clic droit > Exécuter avec PowerShell)
# Étape 1/2 : envoie tes modifs sur GitHub
# Étape 2/2 : ensuite, en SSH sur OVH -> bash deploy-server.sh
# ================================================================
$ErrorActionPreference = "Stop"
Set-Location "C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests"

# Débloque un éventuel verrou git resté coincé (cause des commits qui échouent)
if (Test-Path ".git\index.lock") {
    Remove-Item ".git\index.lock" -Force
    Write-Host "Verrou git supprime" -ForegroundColor Yellow
}

Write-Host "=== 1. Build Vite (assets CSS/JS) ===" -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR npm run build" -ForegroundColor Red; Read-Host; exit 1 }

Write-Host "=== 2. Git add + commit + push ===" -ForegroundColor Cyan
git add -A
git add -f public/build
$msg = "feat: maj complete pour test OVH ($(Get-Date -Format 'yyyy-MM-dd HH:mm'))"
git commit -m $msg
git push origin main
if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR git push" -ForegroundColor Red; Read-Host; exit 1 }

Write-Host ""
Write-Host "=== OK ! Pousse termine. ===" -ForegroundColor Green
Write-Host "Maintenant connecte-toi en SSH sur OVH et lance :" -ForegroundColor Green
Write-Host "    cd ~/praxiquest && bash deploy-server.sh" -ForegroundColor White
Read-Host "Appuyez sur Entree pour fermer"
