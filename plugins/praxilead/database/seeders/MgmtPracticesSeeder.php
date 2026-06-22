<?php

namespace Praxis\Plugins\PraxiLead\Database\Seeders;

use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiLead\Data\Practices;
use Praxis\Plugins\PraxiLead\Models\MgmtPractice;

class MgmtPracticesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Practices::all() as $p) {
            MgmtPractice::updateOrCreate(
                ['day_index' => $p['day']],
                [
                    'theme'           => $p['theme'],
                    'title'           => $p['title'],
                    'summary'         => $p['summary'],
                    'body'            => $p['body'],
                    'micro_challenge' => $p['micro_challenge'],
                    'duration_min'    => $p['duration_min'] ?? 10,
                    'icon'            => $p['icon'] ?? 'compass',
                    'is_active'       => true,
                ]
            );
        }
    }
}
