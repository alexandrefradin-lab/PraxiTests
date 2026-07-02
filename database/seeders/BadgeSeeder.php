<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'slug' => 'first_step', 'name' => 'Premier pas',
                'description' => 'Tu as commencé ton tout premier test.',
                'icon' => 'rocket', 'xp_reward' => 50,
                'criteria' => ['type' => 'first_test'],
            ],
            [
                'slug' => 'completionist', 'name' => 'Complétiste',
                'description' => 'Tu as terminé 3 tests.',
                'icon' => 'check-circle', 'xp_reward' => 150,
                'criteria' => ['type' => 'tests_completed', 'min' => 3],
            ],
            [
                'slug' => 'analyzer', 'name' => 'Analyste',
                'description' => 'Tu as accumulé 500 XP.',
                'icon' => 'chart-bar', 'xp_reward' => 100,
                'criteria' => ['type' => 'xp_total', 'min' => 500],
            ],
            [
                'slug' => 'speedrunner', 'name' => "Rapide comme l'éclair",
                'description' => 'Tu as terminé un test en moins de 10 minutes.',
                'icon' => 'bolt', 'xp_reward' => 75,
                'criteria' => ['type' => 'fast_completion', 'max_seconds' => 600],
            ],
            [
                'slug' => 'introspective', 'name' => 'Introspectif',
                'description' => 'Tu as uploadé ton CV pour aller plus loin.',
                'icon' => 'eye', 'xp_reward' => 50,
                'criteria' => ['type' => 'cv_uploaded'],
            ],
            [
                'slug' => 'eveille', 'name' => 'Éveillé',
                'description' => 'Tu as découvert le secret de l\'Oracle.',
                'icon' => 'sparkles', 'xp_reward' => 0, // XP attribués directement par l\'EasterEggController
                'criteria' => ['type' => 'easter_egg'],
            ],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(['slug' => $b['slug']], $b);
        }
    }
}
