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
        $attempt->answers()->updateOrCreate(
            ['question_id' => $questionId],
            ['value' => $value, 'time_spent_seconds' => $timeSpent]
        );

        $attempt->update(['last_activity_at' => now()]);
        PluginHooks::doAction('attempt.answered', $attempt, $questionId, $value);
    }

    public function complete(TestAttempt $attempt): TestAttempt
    {
        if ($attempt->isComplete()) {
            return $attempt;
        }

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

        PluginHooks::doAction('attempt.completed', $attempt);
        return $attempt->fresh('result');
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
