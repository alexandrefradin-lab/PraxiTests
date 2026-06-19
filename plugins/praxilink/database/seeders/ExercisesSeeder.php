<?php

namespace Praxis\Plugins\PraxiLink\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Praxis\Plugins\PraxiLink\Data\Exercises;

class ExercisesSeeder extends Seeder
{
    /**
     * Table cible pour les exercices du plugin PraxiLink.
     * Adaptez cette constante si votre système utilise un nom de table différent.
     */
    private const TABLE = 'plugin_exercises';

    /**
     * Slug du plugin — utilisé comme namespace pour éviter les collisions.
     */
    private const PLUGIN_SLUG = 'praxilink';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('[PraxiLink] Seeding exercises...');

        $exercises = Exercises::all();
        $now       = Carbon::now();
        $seeded    = 0;
        $updated   = 0;
        $skipped   = 0;

        foreach ($exercises as $exercise) {
            $pluginExerciseId = self::PLUGIN_SLUG . ':' . $exercise['id'];

            // Prépare le payload d'insertion / mise à jour
            $payload = [
                'plugin_slug'        => self::PLUGIN_SLUG,
                'exercise_id'        => $exercise['id'],
                'plugin_exercise_id' => $pluginExerciseId,
                'title'              => $exercise['title'],
                'category'           => $exercise['category'],
                'duration_minutes'   => $exercise['duration_minutes'],
                'difficulty'         => $exercise['difficulty'],
                'scientific_basis'   => $exercise['scientific_basis'],
                'scoring_dimension'  => $exercise['scoring']['dimension']
                    ?? implode(',', array_keys($exercise['scoring']['dimensions'] ?? [])),
                'scoring_weight'     => $exercise['scoring']['weight']
                    ?? 1.0,
                'instructions'       => json_encode($exercise['instructions'], JSON_UNESCAPED_UNICODE),
                'updated_at'         => $now,
            ];

            // Upsert : insert si inexistant, update si déjà présent
            $exists = DB::table(self::TABLE)
                ->where('plugin_exercise_id', $pluginExerciseId)
                ->exists();

            if ($exists) {
                $changed = DB::table(self::TABLE)
                    ->where('plugin_exercise_id', $pluginExerciseId)
                    ->update($payload);

                if ($changed) {
                    $updated++;
                    $this->command?->line("  [updated] {$pluginExerciseId}");
                } else {
                    $skipped++;
                }
            } else {
                DB::table(self::TABLE)->insert(array_merge($payload, [
                    'created_at' => $now,
                ]));
                $seeded++;
                $this->command?->line("  [created] {$pluginExerciseId}");
            }
        }

        $total = count($exercises);
        $this->command?->info(
            "[PraxiLink] Done — {$total} exercises processed: "
            . "{$seeded} created, {$updated} updated, {$skipped} unchanged."
        );

        Log::info('[PraxiLink] ExercisesSeeder completed', [
            'total'   => $total,
            'seeded'  => $seeded,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }
}
