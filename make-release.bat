@echo off
REM ==========================================
REM PraxiTests - Build distribuable (zip pret-a-deployer)
REM Necessite : PHP 8.2+, Composer, Node 20+ INSTALLES
REM ==========================================
setlocal enabledelayedexpansion

set VERSION=1.0.0-alpha
set OUT_DIR=dist
set ZIP_NAME=praxitests-%VERSION%.zip

echo.
echo ========================================
echo  PraxiTests - Build release v%VERSION%
echo ========================================
echo.

REM 1. Nettoyage
if exist "%OUT_DIR%" rmdir /s /q "%OUT_DIR%"
mkdir "%OUT_DIR%"

echo [1/5] composer install --no-dev --optimize-autoloader
call composer install --no-dev --optimize-autoloader --no-interaction
if errorlevel 1 goto :err

echo.
echo [2/5] npm install
call npm install
if errorlevel 1 goto :err

echo.
echo [3/5] npm run build (assets prod)
call npm run build
if errorlevel 1 goto :err

echo.
echo [4/5] Copie des fichiers (exclusion dev)
xcopy /E /I /Y /Q app             "%OUT_DIR%\app\"
xcopy /E /I /Y /Q bootstrap       "%OUT_DIR%\bootstrap\"
xcopy /E /I /Y /Q config          "%OUT_DIR%\config\"
xcopy /E /I /Y /Q database        "%OUT_DIR%\database\"
xcopy /E /I /Y /Q public          "%OUT_DIR%\public\"
xcopy /E /I /Y /Q resources       "%OUT_DIR%\resources\"
xcopy /E /I /Y /Q routes          "%OUT_DIR%\routes\"
xcopy /E /I /Y /Q storage         "%OUT_DIR%\storage\"
xcopy /E /I /Y /Q vendor          "%OUT_DIR%\vendor\"
xcopy /E /I /Y /Q plugins         "%OUT_DIR%\plugins\"
xcopy /E /I /Y /Q docs            "%OUT_DIR%\docs\"
copy artisan        "%OUT_DIR%\"
copy composer.json  "%OUT_DIR%\"
copy composer.lock  "%OUT_DIR%\" 2>nul
copy package.json   "%OUT_DIR%\"
copy .env.example   "%OUT_DIR%\"
copy README.md      "%OUT_DIR%\"
copy INSTALL.md     "%OUT_DIR%\"
copy DEPLOY.md      "%OUT_DIR%\" 2>nul
copy LICENSE        "%OUT_DIR%\" 2>nul

REM Cleanup avant zip
del /Q "%OUT_DIR%\storage\app\.installed" 2>nul
del /Q "%OUT_DIR%\.env" 2>nul

echo.
echo [5/5] Creation du zip %ZIP_NAME%
powershell -Command "Compress-Archive -Path '%OUT_DIR%\*' -DestinationPath '%OUT_DIR%\..\%ZIP_NAME%' -Force"
if errorlevel 1 goto :err

REM Reinstaller les deps dev pour redev
echo.
echo Restauration des dependances dev...
call composer install --no-interaction

echo.
echo ========================================
echo  Build OK : %ZIP_NAME%
echo  Distribuer : envoyer le zip au client
echo  Le client decompresse + ouvre /install.php
echo ========================================
exit /b 0

:err
echo.
echo [X] Erreur durant le build.
exit /b 1
