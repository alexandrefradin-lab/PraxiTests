@echo off
REM ==========================================
REM PraxiTests — Setup Windows
REM ==========================================
setlocal enabledelayedexpansion

echo.
echo ========================================
echo  PraxiTests - Installation locale
echo ========================================
echo.

REM Verifie PHP
where php >nul 2>nul
if errorlevel 1 (
    echo [X] PHP introuvable. Installe PHP 8.2+ depuis https://windows.php.net/download/
    exit /b 1
)
echo [OK] PHP detecte
php -v | findstr "PHP 8"

REM Verifie Composer
where composer >nul 2>nul
if errorlevel 1 (
    echo [X] Composer introuvable. Installe depuis https://getcomposer.org/Composer-Setup.exe
    exit /b 1
)
echo [OK] Composer detecte

REM Verifie Node
where node >nul 2>nul
if errorlevel 1 (
    echo [X] Node.js introuvable. Installe Node 20+ depuis https://nodejs.org
    exit /b 1
)
echo [OK] Node detecte
node -v

echo.
echo --- Installation des dependances PHP (composer) ---
call composer install --no-interaction --prefer-dist
if errorlevel 1 goto :error

echo.
echo --- Installation des dependances JS (npm) ---
call npm install
if errorlevel 1 goto :error

echo.
echo --- Configuration .env ---
if not exist ".env" (
    copy ".env.example" ".env"
    echo .env cree depuis .env.example
) else (
    echo .env existe deja, conserve
)

echo.
echo --- Generation cle d'application ---
call php artisan key:generate --force
if errorlevel 1 goto :error

echo.
echo ========================================
echo  Etape suivante : configure ta base de
echo  donnees dans .env (DB_DATABASE, etc.)
echo  puis relance: setup.bat finish
echo ========================================
echo.

if "%1"=="finish" goto :finish
exit /b 0

:finish
echo.
echo --- Migration + seeders ---
call php artisan storage:link
call php artisan migrate --force
if errorlevel 1 goto :error
call php artisan db:seed --force
if errorlevel 1 goto :error

echo.
echo --- Decouverte et activation des plugins ---
call php artisan praxitests:plugins:discover --sync
for %%P in (praximet praxivaleurs praxicare praxiemo praximum) do (
    echo Activation %%P...
    call php artisan praxitests:plugins:activate %%P
)

echo.
echo --- Build assets (production) ---
call npm run build

echo.
echo ========================================
echo  Installation terminee !
echo  Demarrage : setup.bat run
echo  Login admin : admin@praxitests.local / changeme123
echo ========================================
echo.
exit /b 0

:error
echo.
echo [X] Erreur durant l'installation. Verifie les logs ci-dessus.
exit /b 1
