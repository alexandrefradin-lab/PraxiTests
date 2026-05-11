<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Test;
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

        return Inertia::render('Candidate/TestShow', [
            'test' => $test->load('sections.questions'),
        ]);
    }
}
