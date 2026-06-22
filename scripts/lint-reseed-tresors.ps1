# =============================================================================
#  lint-reseed-tresors.ps1
#  Vérifie (php -l) puis reseed les exercices des 6 apps de la Salle du Trésor.
#  À lancer depuis la RACINE du repo PraxiQuest, sur une machine où `php` existe.
#
#  Usage :
#     .\lint-reseed-tresors.ps1            # lint seul (sécurisé, ne touche pas la BDD)
#     .\lint-reseed-tresors.ps1 -Reseed    # lint + reseed si le lint passe
#     .\lint-reseed-tresors.ps1 -Reseed -Force   # idem, en prod (--force)
# =============================================================================
param(
    [switch]$Reseed,
    [switch]$Force
)

$ErrorActionPreference = "Stop"

# Fichiers d'exercices à vérifier
$files = @(
    "plugins\praxiboost\src\Data\Exercises.php",
    "plugins\praxispeak\src\Data\Exercises.php",
    "plugins\praxiself\src\Data\Exercises.php",
    "plugins\praxilink\src\Data\Exercises.php",
    "plugins\praxizen\src\Data\Exercises.php",
    "plugins\praxiflow\src\Data\Exercises.php"
)

# Seeders correspondants (FQCN)
$seeders = @(
    "Praxis\Plugins\PraxiBoost\Database\Seeders\DevExercisesSeeder",
    "Praxis\Plugins\PraxiSpeak\Database\Seeders\ExercisesSeeder",
    "Praxis\Plugins\PraxiSelf\Database\Seeders\ExercisesSeeder",
    "Praxis\Plugins\PraxiLink\Database\Seeders\ExercisesSeeder",
    "Praxis\Plugins\PraxiZen\Database\Seeders\ExercisesSeeder",
    "Praxis\Plugins\PraxiFlow\Database\Seeders\ExercisesSeeder"
)

Write-Host "=== 1/3  Lint PHP des 6 fichiers d'exercices ===" -ForegroundColor Cyan
$hasError = $false
foreach ($f in $files) {
    if (-not (Test-Path $f)) {
        Write-Host "  MANQUANT  $f" -ForegroundColor Red
        $hasError = $true
        continue
    }
    $out = & php -l $f 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  OK        $f" -ForegroundColor Green
    } else {
        Write-Host "  ERREUR    $f" -ForegroundColor Red
        Write-Host "            $out" -ForegroundColor Red
        $hasError = $true
    }
}

if ($hasError) {
    Write-Host "`nLint en échec : aucun reseed lancé. Corrige les erreurs ci-dessus." -ForegroundColor Red
    exit 1
}
Write-Host "Lint OK sur les 6 fichiers." -ForegroundColor Green

if (-not $Reseed) {
    Write-Host "`n(Pas de -Reseed : on s'arrête au lint. Relance avec -Reseed pour mettre la BDD à jour.)" -ForegroundColor Yellow
    exit 0
}

Write-Host "`n=== 2/3  Synchronisation des plugins ===" -ForegroundColor Cyan
& php artisan plugins:discover --sync

Write-Host "`n=== 3/3  Reseed des exercices (updateOrCreate, non destructif) ===" -ForegroundColor Cyan
$forceFlag = @()
if ($Force) { $forceFlag = @("--force") }
foreach ($s in $seeders) {
    Write-Host "  -> $s" -ForegroundColor Gray
    & php artisan db:seed --class $s @forceFlag
    if ($LASTEXITCODE -ne 0) {
        Write-Host "     Échec du seeder $s" -ForegroundColor Red
        exit 1
    }
}

Write-Host "`nTerminé : lint OK + exercices re-seedés. Les slugs existants sont préservés (updateOrCreate)." -ForegroundColor Green
