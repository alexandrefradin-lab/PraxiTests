<#
  fix-eol-and-commit-praxilink.ps1
  -----------------------------------------------------------------------------
  Nettoie le working tree pollue par du bruit de fins de ligne (CRLF<->LF) et
  isole le SEUL vrai changement : le portage du moteur de scoring praxilink.

  Etapes :
    1. Supprime le verrou git residuel (.git\index.lock)
    2. Configure core.autocrlf=true (empeche le bruit de revenir)
    3. GARDE-FOU : interrompt si un fichier a un vrai changement hors praxilink
    4. Commit UNIQUEMENT le moteur praxilink porte
    5. Jette le bruit EOL des 121 autres fichiers (aucun contenu perdu)

  A lancer depuis la RACINE du repo PraxiQuest (dossier contenant .git).
  -----------------------------------------------------------------------------
#>

$ErrorActionPreference = 'Stop'
$engine = 'plugins/praxilink/src/Scoring/PraxiLinkScoringEngine.php'

# 0. Verif racine repo
if (-not (Test-Path '.git')) {
    Write-Host '[STOP] Lance ce script depuis la racine du repo (dossier contenant .git).' -ForegroundColor Red
    exit 1
}

# 1. Verrou git residuel
if (Test-Path '.git\index.lock') {
    Remove-Item '.git\index.lock' -Force
    Write-Host '[OK]  .git\index.lock supprime' -ForegroundColor Green
} else {
    Write-Host '[--]  Pas de index.lock' -ForegroundColor DarkGray
}

# 2. Config fins de ligne
git config core.autocrlf true
Write-Host '[OK]  core.autocrlf = true' -ForegroundColor Green

# 3. GARDE-FOU : aucun vrai changement (hors espaces/EOL) ne doit exister hors praxilink
$real = git diff --ignore-all-space --numstat |
    Where-Object { $_ -and ($_ -notmatch [regex]::Escape($engine)) } |
    Where-Object {
        $c = $_ -split "`t"
        ($c[0] -ne '0') -or ($c[1] -ne '0')
    }

if ($real) {
    Write-Host '[STOP] Des fichiers ont de VRAIS changements (hors EOL) :' -ForegroundColor Red
    $real | ForEach-Object { Write-Host "        $_" -ForegroundColor Yellow }
    Write-Host '       Verifie-les a la main avant de continuer. Script interrompu (rien jete).' -ForegroundColor Red
    exit 1
}
Write-Host '[OK]  Aucun changement reel hors praxilink (le reste = bruit EOL)' -ForegroundColor Green

# 4. Commit UNIQUEMENT le moteur praxilink
if (git status --porcelain -- $engine) {
    git add -- $engine
    git commit -m "fix(praxilink): port du moteur de scoring sur ScoringEngineContract (score(TestAttempt): array)"
    Write-Host '[OK]  Commit praxilink cree' -ForegroundColor Green
} else {
    Write-Host '[--]  praxilink deja a jour, rien a committer' -ForegroundColor Yellow
}

# 5. Jeter le bruit EOL restant
git checkout -- .
Write-Host '[OK]  Bruit EOL ecarte, working tree propre' -ForegroundColor Green

# 6. Etat final
Write-Host "`n=== git status ===" -ForegroundColor Cyan
git status -s
Write-Host "`n=== dernier commit ===" -ForegroundColor Cyan
git log --oneline -1

Write-Host "`nPret. Etapes suivantes (manuelles) :" -ForegroundColor Cyan
Write-Host "  1. php -l $engine   # lint" -ForegroundColor Gray
Write-Host "  2. .\deploy-ovh.ps1   # build + push" -ForegroundColor Gray
Write-Host "  3. sur OVH : bash deploy-server.sh && php artisan praxiquest:plugins:activate praxilink" -ForegroundColor Gray
