<#
  cleanup-repo.ps1 — Ménage de la racine du dépôt PraxiQuest
  --------------------------------------------------------------
  A LANCER SUR WINDOWS, depuis la racine du repo :
      cd C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiTests
      powershell -ExecutionPolicy Bypass -File .\cleanup-repo.ps1

  Le script est idempotent (relançable sans risque) et NE COMMITTE PAS.
  Il range les fichiers, montre `git status` à la fin, et te laisse
  relire puis committer toi-meme.
#>

$ErrorActionPreference = 'Stop'
Set-Location -Path $PSScriptRoot

function Move-Tracked($file, $destDir) {
    if (Test-Path $file) {
        if (-not (Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir | Out-Null }
        # git mv si suivi, sinon Move-Item
        git ls-files --error-unmatch $file *> $null
        if ($LASTEXITCODE -eq 0) { git mv -f $file (Join-Path $destDir (Split-Path $file -Leaf)) }
        else { Move-Item -Force $file (Join-Path $destDir (Split-Path $file -Leaf)) }
        Write-Host "  -> $file"
    }
}
function Remove-Tracked($file) {
    if (Test-Path $file) {
        git ls-files --error-unmatch $file *> $null
        if ($LASTEXITCODE -eq 0) { git rm -f --quiet $file }
        else { Remove-Item -Force $file }
        Write-Host "  x  $file"
    }
}

Write-Host "`n=== 0. Verrou git residuel + fichier de test ==="
foreach ($f in @('.git\index.lock', '_writetest_')) {
    if (Test-Path $f) { Remove-Item -Force $f; Write-Host "  x  $f" }
}

Write-Host "`n=== 1. Archives ZIP mal nommees / vides (suivies) ==="
# ziCvafWC = zip 'praxitests-ftp', ziaHoX5d = zip 'praxisens' : artefacts FTP
foreach ($f in @('ziCvafWC','ziaHoX5d','praxitests-ftp.zip','praxitests-upload.zip','praxisens.zip')) {
    Remove-Tracked $f
}

Write-Host "`n=== 2. Fichiers vite.config.js.timestamp-*.mjs orphelins ==="
Get-ChildItem -Filter 'vite.config.js.timestamp-*.mjs' | ForEach-Object {
    Remove-Tracked $_.Name
}

Write-Host "`n=== 3. Documentation -> docs/ (README reste a la racine) ==="
$deployDocs = 'DEPLOY.md','DEPLOY-OVH.md','DEPLOY-PERSO-OVH.md','DEPLOY_LOG.md','DEPLOY_STATUS.md','DEBUG_OVH.md','DEBUG_500_RESOLUTION.md','RESOLUTION-500-TESTS-2026-06-20.md'
$auditDocs  = 'AUDIT.md','AUDITS.md','RAPPORT-AUDIT-MULTI-2026-06-19.md','RAPPORT-AUDIT-MULTI-AGENTS-2026-06-21.md','RAPPORT-AUDIT-REALISME-2026-06-21.md','RAPPORT-AUDIT-TESTS-2026-06-21.md','RAPPORT-AVANCEMENT.md'
$planDocs   = 'PLAN_ECLATS_EXERCICES.md','PLAN_GRIMOIRE_GLOBAL.md','PROPOSITION-REFONTE-TRESORS-2026-06-21.md','brief-logo-praxiquest.md','prompt-15-pistes-metiers.md','DESIGN_BRIEF_PRAXIQUEST.md','JOURNAL.md','HANDOFF.md','HANDOVER.md'
$guideDocs  = 'ARCHITECTURE.md','INSTALL.md','QUICKSTART.md'

foreach ($f in $deployDocs) { Move-Tracked $f 'docs\deploy' }
foreach ($f in $auditDocs)  { Move-Tracked $f 'docs\audits' }
foreach ($f in $planDocs)   { Move-Tracked $f 'docs\archive' }
foreach ($f in $guideDocs)  { Move-Tracked $f 'docs' }

Write-Host "`n=== 4. Scripts ponctuels -> scripts/ (deploy-ovh.ps1 + deploy-server.sh restent) ==="
$miscScripts = 'fix-eol-and-commit-praxilink.ps1','lint-reseed-tresors.ps1','lint-reseed-tresors.sh','build-and-push.ps1','rebuild-full.ps1','make-release.bat','make-release.sh','deploy.sh','dev.bat','dev.sh','setup.bat','setup.sh'
foreach ($f in $miscScripts) { Move-Tracked $f 'scripts' }

Write-Host "`n=== 5. Mise a jour du .gitignore ==="
$gi = '.gitignore'
$rules = @(
    '',
    '# --- Menage 2026-06-22 ---',
    '*.zip',
    'vite.config.js.timestamp-*.mjs',
    '_writetest_',
    'ziCvafWC',
    'ziaHoX5d'
)
$existing = if (Test-Path $gi) { Get-Content $gi } else { @() }
foreach ($r in $rules) {
    if ($r -eq '' -or ($existing -notcontains $r)) { Add-Content -Path $gi -Value $r }
}
Write-Host "  .gitignore mis a jour"

Write-Host "`n=== TERMINE — relis puis committe ==="
git status -s
Write-Host "`nSi tout est OK :"
Write-Host '   git add -A'
Write-Host '   git commit -m "chore: menage racine (docs/, scripts/, .gitignore, suppression artefacts)"'
Write-Host "   .\deploy-ovh.ps1   # quand tu veux pousser en prod"
