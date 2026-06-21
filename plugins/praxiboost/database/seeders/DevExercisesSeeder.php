<?php

namespace Praxis\Plugins\PraxiBoost\Database\Seeders;

use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiBoost\Data\Exercises;
use Praxis\Plugins\PraxiBoost\Models\DevExercise;

class DevExercisesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Exercises::all() as $ex) {
            DevExercise::updateOrCreate(
                ['slug' => $ex['slug']],
                [
                    'title'            => $ex['title'],
                    'category'         => $ex['category'],
                    'summary'          => $ex['summary'],
                    'body'             => $ex['body'],
                    'duration_min'     => $ex['duration_min'],
                    'icon'             => $ex['icon'],
                    'threshold_eclats' => $ex['threshold_eclats'],
                    'sort_order'       => $ex['sort_order'],
                    'is_active'        => true,
                ]
            );
        }
    }
}
