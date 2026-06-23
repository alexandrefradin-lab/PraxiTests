<?php

namespace Praxis\Plugins\PraxiZenith\Database\Seeders;

use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiZenith\Data\Exercises;
use Praxis\Plugins\PraxiZenith\Models\FocusExercise;

class FocusExercisesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Exercises::all() as $e) {
            FocusExercise::updateOrCreate(
                ['day_index' => $e['day']],
                [
                    'theme'           => $e['theme'],
                    'title'           => $e['title'],
                    'summary'         => $e['summary'],
                    'body'            => $e['body'],
                    'micro_challenge' => $e['micro_challenge'],
                    'duration_min'    => $e['duration_min'] ?? 10,
                    'icon'            => $e['icon'] ?? 'eye',
                    'is_active'       => true,
                ]
            );
        }
    }
}
