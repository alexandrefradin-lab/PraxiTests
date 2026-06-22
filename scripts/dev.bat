@echo off
REM Mode dev : serveur Laravel + Vite + Queue worker en parallele
echo Demarrage PraxiQuest en mode dev...
echo URL : http://127.0.0.1:8000
echo.
start "Laravel server" cmd /k "php artisan serve"
start "Vite dev"        cmd /k "npm run dev"
start "Queue worker"    cmd /k "php artisan queue:work --tries=3"
echo Trois fenetres lancees. Ctrl+C dans chacune pour arreter.
