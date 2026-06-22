#!/usr/bin/env bash
# =============================================================================
#  lint-reseed-tresors.sh
#  Vérifie (php -l) puis reseed les exercices des 6 apps de la Salle du Trésor.
#  À lancer depuis la RACINE du repo PraxiQuest (ex. sur OVH après git pull).
#
#  Usage :
#     ./lint-reseed-tresors.sh            # lint seul (ne touche pas la BDD)
#     ./lint-reseed-tresors.sh --reseed   # lint + reseed si le lint passe (--force inclus, prod)
# =============================================================================
set -u

# Sur OVH, php peut s'appeler php8.2/php8.3 : on prend le 1er dispo.
PHP="${PHP:-php}"
command -v "$PHP" >/dev/null 2>&1 || PHP=php8.3
command -v "$PHP" >/dev/null 2>&1 || PHP=php8.2
command -v "$PHP" >/dev/null 2>&1 || PHP=php8.1

FILES=(
  "plugins/praxiboost/src/Data/Exercises.php"
  "plugins/praxispeak/src/Data/Exercises.php"
  "plugins/praxiself/src/Data/Exercises.php"
  "plugins/praxilink/src/Data/Exercises.php"
  "plugins/praxizen/src/Data/Exercises.php"
  "plugins/praxiflow/src/Data/Exercises.php"
)

SEEDERS=(
  'Praxis\Plugins\PraxiBoost\Database\Seeders\DevExercisesSeeder'
  'Praxis\Plugins\PraxiSpeak\Database\Seeders\ExercisesSeeder'
  'Praxis\Plugins\PraxiSelf\Database\Seeders\ExercisesSeeder'
  'Praxis\Plugins\PraxiLink\Database\Seeders\ExercisesSeeder'
  'Praxis\Plugins\PraxiZen\Database\Seeders\ExercisesSeeder'
  'Praxis\Plugins\PraxiFlow\Database\Seeders\ExercisesSeeder'
)

echo "=== 1/3  Lint PHP des 6 fichiers d'exercices (avec $PHP) ==="
err=0
for f in "${FILES[@]}"; do
  if [ ! -f "$f" ]; then echo "  MANQUANT  $f"; err=1; continue; fi
  if out=$("$PHP" -l "$f" 2>&1); then
    echo "  OK        $f"
  else
    echo "  ERREUR    $f"; echo "            $out"; err=1
  fi
done

if [ "$err" -ne 0 ]; then
  echo; echo "Lint en échec : aucun reseed lancé."; exit 1
fi
echo "Lint OK sur les 6 fichiers."

if [ "${1:-}" != "--reseed" ]; then
  echo; echo "(Pas de --reseed : on s'arrête au lint. Relance avec --reseed pour la BDD.)"; exit 0
fi

echo; echo "=== 2/3  Synchronisation des plugins ==="
"$PHP" artisan plugins:discover --sync

echo; echo "=== 3/3  Reseed des exercices (updateOrCreate, non destructif) ==="
for s in "${SEEDERS[@]}"; do
  echo "  -> $s"
  "$PHP" artisan db:seed --class="$s" --force || { echo "     Échec du seeder $s"; exit 1; }
done

echo; echo "Terminé : lint OK + exercices re-seedés (slugs existants préservés)."
