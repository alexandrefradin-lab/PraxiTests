<?php

namespace Praxis\Plugins\PraxiVision\Database\Seeders;

use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiVision\Data\Practices;
use Praxis\Plugins\PraxiVision\Models\VisionPractice;

class VisionPracticesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Practices::all() as $p) {
            VisionPractice::updateOrCreate(
                ['day_index' => $p['day']],
                [
                    'theme'           => $p['theme'],
                    'title'           => $p['title'],
                    'summary'         => $p['summary'],
                    'body'            => $p['body'],
                    'micro_challenge' => $p['micro_challenge'],
                    'duration_min'    => $p['duration_min'] ?? 12,
                    'icon'            => $p['icon'] ?? 'compass',
                    'is_active'       => true,
                ]
            );
        }
    }
}
