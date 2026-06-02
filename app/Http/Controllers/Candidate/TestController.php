<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestAttempt;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::where('published', true)->get(['id','slug','name','description','estimated_minutes']);

        return Inertia::render('Candidate/TestsIndex', [
            'tests' => $tests,
            'profile_complete' => auth()->user()->profile?->isComplete() ?? false,
        ]);
    }

    public function show(Test $test)
    {
        abort_unless($test->published, 404);

        $user = auth()->user();

        $inProgress = TestAttempt::where('user_id', $user->id)
            ->where('test_id', $test->id)
            ->where('status', 'in_progress')
            ->first();

        $alreadyCompleted = TestAttempt::where('user_id', $user->id)
            ->where('test_id', $test->id)
            ->where('status', 'completed')
            ->exists();

        return Inertia::render('Candidate/TestShow', [
            'test'               => $test->load('sections.questions'),
            'profile_complete'   => $user->profile?->isComplete() ?? false,
            'already_attempted'  => $alreadyCompleted,
            'attempt_in_progress' => $inProgress ? $inProgress->only('id') : null,
        ]);
    }
}
