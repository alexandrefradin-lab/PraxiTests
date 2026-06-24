# ================================================================
# PraxiQuest — Déploiement FULL (build + commit + push + SSH OVH)
# À lancer sur Windows (clic droit > Exécuter avec PowerShell)
# ================================================================
$ErrorActionPreference = "Stop"
Set-Location "C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests"

$SSH_USER = "fa7386-ovh"
$SSH_HOST = "ssh.cluster121.hosting.ovh.net"
$SSH_CMD  = "cd ~/praxiquest && bash deploy-server.sh"

# Débloque un éventuel verrou git resté coincé
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
Write-Host "=== 3. Deploy SSH sur OVH ===" -ForegroundColor Cyan
Write-Host "Connexion a ${SSH_USER}@${SSH_HOST}..." -ForegroundColor Yellow
ssh "${SSH_USER}@${SSH_HOST}" $SSH_CMD
if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR SSH deploy" -ForegroundColor Red; Read-Host; exit 1 }

Write-Host ""
Write-Host "=== DEPLOY COMPLET ===" -ForegroundColor Green
Write-Host "https://praxiquest.decisionpro.fr" -ForegroundColor White
Read-Host "Appuyez sur Entree pour fermer"
