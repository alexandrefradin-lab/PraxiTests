#!/usr/bin/env bash
# Lance Laravel + Vite + Queue en parallèle (3 panneaux)
echo "PraxiTests dev — http://127.0.0.1:8000"
trap 'kill 0' EXIT
php artisan serve &
npm run dev &
php artisan queue:work --tries=3 &
wait
