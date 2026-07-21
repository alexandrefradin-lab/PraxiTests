# ================================================================
# PraxiQuest - Deploiement LOCAL (build + commit + push)
# A lancer sur Windows (clic droit > Executer avec PowerShell)
#
# Etape 1/2 : ce script envoie tes modifs sur GitHub, sur la branche
#             COURANTE (plus de "main" en dur : pousser main depuis une
#             branche de feature echouait en non-fast-forward).
# Etape 2/2 : ensuite, en SSH sur OVH -> cd ~/praxiquest && bash deploy-server.sh
#             (deploy-server.sh deploie ce qui est sur main : si tu es sur une
#              branche, il faut d'abord merger la PR.)
# ================================================================
$ErrorActionPreference = "Stop"
Set-Location "C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests"

# Debloque un eventuel verrou git reste coince (cause des commits qui echouent)
if (Test-Path ".git\index.lock") {
    Remove-Item ".git\index.lock" -Force
    Write-Host "Verrou git supprime" -ForegroundColor Yellow
}

# --- Branche courante : tout le script s'aligne dessus ---
$branch = (git rev-parse --abbrev-ref HEAD).Trim()
if (-not $branch -or $branch -eq "HEAD") {
    Write-Host "ERREUR : HEAD detachee, impossible de determiner la branche." -ForegroundColor Red
    Read-Host; exit 1
}
Write-Host "Branche courante : $branch" -ForegroundColor Cyan

Write-Host "=== 1. Build Vite (assets CSS/JS) ===" -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR npm run build" -ForegroundColor Red; Read-Host; exit 1 }

Write-Host "=== 2. Git add + commit ===" -ForegroundColor Cyan
git add -A
git add -f public/build

# Rien a committer : ce n'est pas une erreur (on peut vouloir juste re-pousser).
git diff --cached --quiet
$rienAcommitter = ($LASTEXITCODE -eq 0)

if ($rienAcommitter) {
    Write-Host "Aucune modification a committer - on passe au push." -ForegroundColor Yellow
} else {
    # Demander un vrai message de commit pour la tracabilite
    $msg = Read-Host "Message de commit (ex: fix: trailing comma plugin.json)"
    if (-not $msg) {
        $msg = "chore: maj $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
        Write-Host "Message par defaut : $msg" -ForegroundColor Yellow
    }
    git commit -m $msg
    if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR git commit" -ForegroundColor Red; Read-Host; exit 1 }
}

Write-Host "=== 3. Push vers origin/$branch ===" -ForegroundColor Cyan
git push -u origin $branch
if ($LASTEXITCODE -ne 0) { Write-Host "ERREUR git push" -ForegroundColor Red; Read-Host; exit 1 }

Write-Host ""
Write-Host "=== OK ! Push termine sur $branch ===" -ForegroundColor Green

if ($branch -eq "main") {
    Write-Host "Tu es sur main : deploie directement." -ForegroundColor Green
    Write-Host "    cd ~/praxiquest && bash deploy-server.sh" -ForegroundColor White
} else {
    # deploy-server.sh fait 'git pull origin main' : deployer avant le merge
    # ne remonterait rien (constate le 21/07/2026, deux deploiements pour rien).
    Write-Host "Tu es sur une branche : deployer MAINTENANT ne remonterait rien." -ForegroundColor Yellow
    Write-Host "Enchainement :" -ForegroundColor Yellow
    Write-Host "  1. Ouvrir la PR   https://github.com/alexandrefradin-lab/PraxiTests/compare/main...$branch" -ForegroundColor White
    Write-Host "  2. Attendre la CI verte (4 jobs)" -ForegroundColor White
    Write-Host "  3. Squash and merge" -ForegroundColor White
    Write-Host "  4. cd ~/praxiquest && bash deploy-server.sh" -ForegroundColor White
}
Read-Host "Appuyez sur Entree pour fermer"
