<?php

namespace Praxis\Core\TestEngine;

use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Praxis\Core\Plugins\PluginHooks;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\Scoring\DefaultScoringEngine;

class TestEngine
{
    /** @var array<string, ScoringEngineContract> */
    protected array $scoringEngines = [];

    public function __construct()
    {
        $this->registerScoringEngine(new DefaultScoringEngine());
    }

    public function registerScoringEngine(ScoringEngineContract $engine): void
    {
        $this->scoringEngines[$engine->key()] = $engine;
    }

    /** @return string[] Clés des moteurs de scoring enregistrés (pour l'éditeur de tests) */
    public function availableEngines(): array
    {
        return array_keys($this->scoringEngines);
    }

    public function startAttempt(User $user, Test $test, ?int $invitationId = null): TestAttempt
    {
        // QC-14 : vérification + création sous transaction et lock pour éviter
        // la création concurrente de deux tentatives (double-clic, double requête).
        return DB::transaction(function () use ($user, $test, $invitationId) {
            $existing = TestAttempt::where('user_id', $user->id)
                ->where('test_id', $test->id)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $attempt = TestAttempt::create([
                'user_id'          => $user->id,
                'test_id'          => $test->id,
                'invitation_id'    => $invitationId,
                'status'           => 'in_progress',
                'started_at'       => now(),
                'last_activity_at' => now(),
                'progress'         => [],
            ]);

            PluginHooks::doAction('attempt.started', $attempt);
            return $attempt;
        });
    }

    public function recordAnswer(TestAttempt $attempt, int $questionId, mixed $value, int $timeSpent = 0): void
    {
        // Compter les vrais changements d'avis (easter egg « Le Doute ») : on
        // n'incrémente que si une réponse existait ET que la valeur diffère.
        // L'autosave renvoie régulièrement la même valeur — la compter ferait
        // passer pour de l'hésitation ce qui n'est qu'un enregistrement.
        $existing = $attempt->answers()->where('question_id', $questionId)->first();
        $changed  = $existing !== null && $existing->value !== $value;

        $attempt->answers()->updateOrCreate(
            ['question_id' => $questionId],
            [
                'value'              => $value,
                'time_spent_seconds' => $timeSpent,
                'revisions'          => ($existing->revisions ?? 0) + ($changed ? 1 : 0),
            ]
        );

        $attempt->update(['last_activity_at' => now()]);
        PluginHooks::doAction('attempt.answered', $attempt, $questionId, $value);
    }

    public function complete(TestAttempt $attempt): TestAttempt
    {
        if ($attempt->isComplete()) {
            return $attempt;
        }

        // Audit risque #3 — vérifier que toutes les questions obligatoires
        // ont reçu une réponse avant d'accepter la complétion.
        $this->assertAllRequiredAnswered($attempt);

        $attempt->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        $scoring = $this->resolveScoringEngine($attempt->test)->score($attempt);
        $scoring = PluginHooks::applyFilters('attempt.scoring', $scoring, $attempt);

        $attempt->result()->updateOrCreate(
            [],
            [
                'scoring'      => $scoring,
                'generated_at' => now(),
            ]
        );

        // Marquer l'invitation comme complétée
        if ($attempt->invitation_id) {
            \App\Models\TestInvitation::where('id', $attempt->invitation_id)
                ->update(['status' => 'completed']);
        }

        PluginHooks::doAction('attempt.completed', $attempt);
        return $attempt->fresh('result');
    }

    /**
     * Vérifie que toutes les questions marquées `required` ont une réponse
     * dans cette tentative. Lance une InvalidArgumentException sinon.
     */
    protected function assertAllRequiredAnswered(TestAttempt $attempt): void
    {
        // IDs des questions obligatoires du test
        $requiredIds = \App\Models\TestQuestion::query()
            ->whereHas('section', fn ($q) => $q->where('test_id', $attempt->test_id))
            ->where('required', true)
            ->pluck('id');

        if ($requiredIds->isEmpty()) {
            return;
        }

        // IDs des questions déjà répondues dans cette tentative
        $answeredIds = $attempt->answers()->pluck('question_id');

        $missing = $requiredIds->diff($answeredIds);

        if ($missing->isNotEmpty()) {
            throw new \InvalidArgumentException(
                "Impossible de terminer le test : {$missing->count()} question(s) obligatoire(s) sans réponse."
            );
        }
    }

    public function resolveScoringEngine(Test $test): ScoringEngineContract
    {
        $key = $test->scoring_engine ?: 'default';
        if (!isset($this->scoringEngines[$key])) {
            throw new InvalidArgumentException("Unknown scoring engine: {$key}");
        }
        return $this->scoringEngines[$key];
    }
}
