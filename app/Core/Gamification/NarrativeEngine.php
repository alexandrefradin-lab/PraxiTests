<?php

namespace Praxis\Core\Gamification;

use App\Models\TestAttempt;
use App\Models\TestQuestion;
use Praxis\Core\TestEngine\TestEngine;

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

    /**
     * Encouragements aléatoires pendant le test (Zeigarnik).
     *
     * La promesse « débloquer un aperçu » n'est ajoutée au pool QUE si le test
     * est réellement scoré et que l'aperçu n'est pas encore atteignable —
     * sinon ce serait un leurre. L'aperçu lui-même est servi par insight().
     */
    public function microFeedback(TestAttempt $attempt, float $progressPercent): ?string
    {
        if ($progressPercent < 25) return null;

        $messages = [
            'Continue, ton profil prend forme.',
            'Belle progression. Garde ce rythme.',
            'Chaque réponse affine ta cartographie.',
            'Tu approches d\'un palier important.',
        ];

        if ($this->insightPromiseRelevant($attempt, $progressPercent)) {
            $messages[] = 'Encore quelques questions pour débloquer un aperçu de ton profil.';
        }

        return $messages[array_rand($messages)];
    }

    /**
     * Aperçu provisoire calculé à partir des réponses DÉJÀ données.
     * Renvoie null tant que le palier (config gamification.insight) n'est pas
     * franchi ou qu'aucune dimension n'est encore mesurable.
     *
     * @return array{dimension:string,label:string,score:float,headline:string,body:string,provisional:bool}|null
     */
    public function insight(TestAttempt $attempt, float $progressPercent): ?array
    {
        $cfg = config('gamification.insight', []);
        $minPercent = (float) ($cfg['min_percent'] ?? 40);
        $minAnswers = (int) ($cfg['min_answers'] ?? 4);

        if ($progressPercent < $minPercent) return null;
        if ($attempt->answers()->count() < $minAnswers) return null;

        $dimensions = $this->partialDimensions($attempt);
        if (empty($dimensions)) return null;

        arsort($dimensions);
        $slug  = (string) array_key_first($dimensions);
        $score = (float) $dimensions[$slug];
        $label = $this->humanizeDimension($slug);

        return [
            'dimension'   => $slug,
            'label'       => $label,
            'score'       => round($score, 1),
            'headline'    => $this->insightHeadline($label, $score),
            'body'        => $this->insightBody($label, $score),
            'provisional' => true,
        ];
    }

    /**
     * Dimensions normalisées (0-100) calculées sur les réponses partielles via
     * le moteur de scoring du test. Toute panne (moteur plugin exigeant une
     * tentative terminée, etc.) retombe silencieusement sur "pas d'aperçu".
     *
     * @return array<string,float>
     */
    protected function partialDimensions(TestAttempt $attempt): array
    {
        try {
            $scoring = app(TestEngine::class)
                ->resolveScoringEngine($attempt->test)
                ->score($attempt);
        } catch (\Throwable $e) {
            logger()->warning("NarrativeEngine::partialDimensions a échoué pour attempt #{$attempt->id}: {$e->getMessage()}");
            return [];
        }

        $dims = $scoring['dimensions'] ?? [];
        if (! is_array($dims)) return [];

        return array_filter($dims, fn ($v) => is_numeric($v));
    }

    /** La promesse d'aperçu n'a de sens que si le test est scoré et l'aperçu encore verrouillé. */
    protected function insightPromiseRelevant(TestAttempt $attempt, float $progressPercent): bool
    {
        if ($this->insight($attempt, $progressPercent) !== null) {
            return false; // déjà débloqué : on ne promet plus, on montre.
        }

        return TestQuestion::whereHas('section', fn ($q) => $q->where('test_id', $attempt->test_id))
            ->whereNotNull('scoring')
            ->exists();
    }

    /** Transforme un slug de dimension ("dim_analytique", "esprit-equipe") en libellé lisible. */
    protected function humanizeDimension(string $slug): string
    {
        $s = preg_replace('/^(dim|score|trait)[_-]/i', '', $slug);
        $s = str_replace(['_', '-'], ' ', (string) $s);
        $s = trim($s);

        return $s === '' ? $slug : mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

    protected function insightHeadline(string $label, float $score): string
    {
        if ($score >= 66) return "{$label} ressort nettement";
        if ($score >= 40) return "{$label} se dessine";
        return "{$label} commence à émerger";
    }

    protected function insightBody(string $label, float $score): string
    {
        $strength = $score >= 66 ? 'fortement' : ($score >= 40 ? 'clairement' : 'légèrement');

        return "D'après tes réponses jusqu'ici, la dimension « {$label} » se distingue {$strength}. "
            . "C'est un aperçu provisoire : il s'affinera à chaque nouvelle réponse.";
    }
}
