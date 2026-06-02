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

    public function startAttempt(User $user, Test $test, ?int $invitationId = null): TestAttempt
    {
        return DB::transaction(function () use ($user, $test, $invitationId) {
            // Vérification sous lock pour éviter la création concurrente
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
            