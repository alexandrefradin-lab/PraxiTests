<?php

namespace Praxis\Plugins\PraxiCog;

use Praxis\Core\Plugins\AbstractPlugin;
use Praxis\Core\TestEngine\TestEngine;

class PluginServiceProvider extends AbstractPlugin
{
    public function register(): void
    {
        // Rien à lier dans le container.
    }

    public function boot(): void
    {
        // ── Moteur de scoring (bonne/mauvaise réponse, 0/1) ───────────────
        $this->app->make(TestEngine::class)
            ->registerScoringEngine(new Scoring\PraxiCogScoringEngine());

        // ── Page de résultats dédiée ──────────────────────────────────────
        // 1) autoriser la page dans la whitelist du ResultController
        // 2) router la tentative vers cette page quand c'est notre test
        $this->registerFilters([
            'results.allowed_pages' => fn (array $pages) =>
                array_merge($pages, ['PraxiCogResult']),

            'results.inertia_page' => fn (string $page, $attempt) =>
                ($attempt->test->scoring_engine ?? null) === 'praxicog-scoring'
                    ? 'PraxiCogResult'
                    : $page,

            // ── Synthèse IA taillée « aptitude » (jamais de QI / trait figé) ──
            // Remplace le persona générique du cœur uniquement pour nos tentatives.
            'ai.synthesis.messages' => function (array $messages, $attempt) {
                if (($attempt->test->scoring_engine ?? null) !== 'praxicog-scoring') {
                    return $messages;
                }
                return Support\AptitudeSynthesisPrompt::messages($attempt);
            },

            // Filet de sécurité : neutralise tout vocabulaire interdit qui aurait
            // franchi les garde-fous du prompt (ceinture + bretelles).
            'ai.synthesis.output' => function (string $text, $attempt) {
                if (($attempt->test->scoring_engine ?? null) !== 'praxicog-scoring') {
                    return $text;
                }
                // ?? $text : si preg_replace échoue (UTF-8 invalide), on garde le
                // texte original plutôt que de renvoyer null (synthèse vide).
                return preg_replace(
                    '/\b(quotient intellectuel|QI|haut potentiel|surdou\p{L}*)\b/iu',
                    'aptitude au raisonnement',
                    $text
                ) ?? $text;
            },
        ]);
    }

    /**
     * Activation idempotente : (re)seed des questions puis des normes.
     */
    public function onActivate(): void
    {
        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiCog\\Database\\Seeders\\QuestionsSeeder',
            '--force' => true,
        ]);

        \Artisan::call('db:seed', [
            '--class' => 'Praxis\\Plugins\\PraxiCog\\Database\\Seeders\\NormsSeeder',
            '--force' => true,
        ]);
    }
}
