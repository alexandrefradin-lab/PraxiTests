<?php

namespace Praxis\Core\Gamification;

use App\Models\TestAttempt;

class NarrativeEngine
{
    /**
     * Renvoie le micro-message à afficher à un moment du parcours :
     *  - intro      : début du test
     *  - section    : début d'une section
     *  - midway     : ~50% de progression
     *  - section_end : fin d'une section
     *  - final      : dernière question
     *  - completion : fin du test
     */
    public function messageFor(string $stage, TestAttempt $attempt): string
    {
        $config = $attempt->test->gamification['narrative'] ?? [];
        $defaults = config('gamification.narrative');

        $custom = $config[$stage] ?? null;
        if ($custom) return $custom;

        return $defaults[$stage] ?? '';
    }

    /** Encouragements aléatoires pendant le test (Zeigarnik) */
    public function microFeedback(TestAttempt $attempt, float $progressPercent): ?string
    {
        if ($progressPercent < 25) return null;

        $messages = [
            'Continue, ton profil prend forme.',
            'Belle progression. Garde ce rythme.',
            'Chaque réponse affine ta cartographie.',
            'Tu approches d\'un palier important.',
            'Encore quelques questions pour débloquer un insight.',
        ];

        return $messages[array_rand($messages)];
    }
}
