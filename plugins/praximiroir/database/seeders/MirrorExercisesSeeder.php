<?php

namespace Praxis\Plugins\PraxiMiroir\Database\Seeders;

use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiMiroir\Data\Exercises;
use Praxis\Plugins\PraxiMiroir\Models\MirrorExercise;

class MirrorExercisesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Exercises::all() as $e) {
            MirrorExercise::updateOrCreate(
                ['day_index' => $e['day']],
                [
                    'bloc'         => $e['bloc'],
                    'title'        => $e['title'],
                    'summary'      => $e['summary'],
                    'body'         => $e['body'],
                    'prompt'       => $e['prompt'],
                    'duration_min' => $e['duration_min'] ?? 15,
                    'icon'         => $e['icon'] ?? 'mirror',
                    'is_active'    => true,
                ]
            );
        }
    }
}
