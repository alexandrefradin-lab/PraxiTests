<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAttemptInsights;
use App\Models\Test;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\NarrativeEngine;
use Praxis\Core\TestEngine\TestEngine;

class AttemptController extends Controller
{
    public function __construct(
        protected TestEngine $engine,
        protected GamificationEngine $gamification,
        protected NarrativeEngine $narrative,
    ) {}

    public function start(Request $request, Test $test)
    {
        abort_unless($test->published, 404);
        abort_unless(auth()->user()->profile?->isComplete(), 403, 'Profil incomplet');

        $attempt = $this->engine->startAttempt($request->user(), $test);

        return redirect()->route('attempt.show', $attempt);
    }

    public function show(TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        return Inertia::render('Candidate/AttemptPlay', [
            'attempt'    => $attempt->load('test.sections.questions', 'answers'),
            'progress'   => [
                'percent' => $attempt->progressPercent(),
                'narrative' => $this->narrative->microFeedback($attempt, $attempt->progressPercent()),
            ],
            'gamification' => $this->gamification->progressOf($attempt->user, $attempt->test),
            'narrative'    => [
                'intro'   => $this->narrative->messageFor('intro', $attempt),
                'midway'  => $this->narrative->messageFor('midway', $attempt),
                'final'   => $this->narrative->messageFor('final', $attempt),
            ],
        ]);
    }

    public function answer(Request $request, TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'value'       => ['required'],
            'time_spent'  => ['nullable', 'integer'],
        ]);

        $this->engine->recordAnswer($attempt, (int) $data['question_id'], $data['value'], (int) ($data['time_spent'] ?? 0));

        return back();
    }

    public function complete(TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        $this->engine->complete($attempt);
        GenerateAttemptInsights::dispatch($attempt->id);

        return redirect()->route('results.show', $attempt);
    }

    protected function authorizeAttempt(TestAttempt $attempt): void
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
    }
}
