<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        // 'icon' : nom Tabler (rendu « ti ti-<icon> » côté Vue).
        // 'hidden' : le badge n'est pas listé tant qu'il n'est pas obtenu —
        //   réservé aux easter eggs, dont l'énoncé révélerait le secret.
        $badges = [
            [
                'slug' => 'first_step', 'name' => 'Premier pas',
                'description' => 'Tu as commencé ton tout premier test.',
                'name_corporate' => 'Premier pas',
                'description_corporate' => 'Vous avez démarré votre première évaluation.',
                'icon' => 'rocket', 'xp_reward' => 50,
                'criteria' => ['type' => 'first_test'],
            ],
            [
                'slug' => 'completionist', 'name' => 'Complétiste',
                'description' => 'Tu as terminé 3 tests.',
                'name_corporate' => 'Assiduité',
                'description_corporate' => 'Vous avez terminé trois évaluations.',
                'icon' => 'circle-check', 'xp_reward' => 150,
                'criteria' => ['type' => 'tests_completed', 'min' => 3],
            ],
            [
                'slug' => 'analyzer', 'name' => 'Analyste',
                'description' => 'Tu as accumulé 500 XP.',
                'name_corporate' => 'Analyste',
                'description_corporate' => 'Vous avez accumulé 500 points.',
                'icon' => 'chart-bar', 'xp_reward' => 100,
                'criteria' => ['type' => 'xp_total', 'min' => 500],
            ],
            [
                'slug' => 'speedrunner', 'name' => "Rapide comme l'éclair",
                'description' => 'Tu as terminé un test en moins de 10 minutes.',
                'name_corporate' => 'Efficacité',
                'description_corporate' => 'Vous avez terminé une évaluation en moins de dix minutes.',
                'icon' => 'bolt', 'xp_reward' => 75,
                'criteria' => ['type' => 'fast_completion', 'max_seconds' => 600],
            ],
            [
                'slug' => 'introspective', 'name' => 'Introspectif',
                'description' => 'Tu as uploadé ton CV pour aller plus loin.',
                'name_corporate' => 'Démarche approfondie',
                'description_corporate' => 'Vous avez transmis votre CV pour affiner l\'analyse.',
                'icon' => 'eye', 'xp_reward' => 50,
                'criteria' => ['type' => 'cv_uploaded'],
            ],

            // ── Easter eggs (XP attribués par EasterEggController, pas ici) ──
            [
                'slug' => 'eveille', 'name' => 'Éveillé',
                'description' => 'Tu as découvert le secret de l\'Oracle.',
                'name_corporate' => 'Perspicacité',
                'description_corporate' => 'Vous avez découvert une fonction non documentée.',
                'icon' => 'sparkles', 'xp_reward' => 0,
                'hidden' => true,
                'criteria' => ['type' => 'easter_egg'],
            ],
            [
                'slug' => 'egare', 'name' => 'Égaré',
                'description' => 'Tu as suivi un chemin qui n\'existait pas.',
                'name_corporate' => 'Exploration',
                'description_corporate' => 'Vous avez suivi un chemin qui n\'existait pas.',
                'icon' => 'compass-off', 'xp_reward' => 0,
                'hidden' => true,
                'criteria' => ['type' => 'easter_egg'],
            ],
            [
                'slug' => 'scribe', 'name' => 'Scribe',
                'description' => 'Tu as lu le Grimoire à l\'envers.',
                'name_corporate' => 'Relecture critique',
                'description_corporate' => 'Vous avez parcouru votre dossier de synthèse à rebours.',
                'icon' => 'feather', 'xp_reward' => 0,
                'hidden' => true,
                'criteria' => ['type' => 'easter_egg'],
            ],
            [
                'slug' => 'dechiffreur', 'name' => 'Déchiffreur',
                'description' => 'Tu as lu ce qui était écrit à l\'encre invisible.',
                'name_corporate' => 'Lecture attentive',
                'description_corporate' => 'Vous avez trouvé une note dissimulée dans votre dossier.',
                'icon' => 'eyeglass', 'xp_reward' => 0,
                'hidden' => true,
                'criteria' => ['type' => 'easter_egg'],
            ],
            [
                'slug' => 'nuance', 'name' => 'Nuance',
                'description' => 'Tu as changé cinq fois d\'avis sur la même question. Ce n\'est pas de l\'indécision.',
                'name_corporate' => 'Nuance',
                'description_corporate' => 'Vous avez révisé cinq fois votre réponse à une même question.',
                'icon' => 'scale', 'xp_reward' => 0,
                'hidden' => true,
                'criteria' => ['type' => 'easter_egg'],
            ],
            [
                // Méta-badge : ne s'attribue que lorsque TOUS les autres sont
                // obtenus, secrets compris. Caché, sinon il annoncerait le
                // nombre exact de secrets restants.
                'slug' => 'constellation', 'name' => 'Constellation',
                'description' => 'Tu as réuni tous les hauts faits. Il n\'en restait aucun.',
                'name_corporate' => 'Parcours complet',
                'description_corporate' => 'Vous avez obtenu l\'ensemble des distinctions.',
                'icon' => 'stars', 'xp_reward' => 100,
                'hidden' => true,
                'criteria' => ['type' => 'all_badges'],
            ],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(['slug' => $b['slug']], $b);
        }
    }
}
