<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestEditorController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Tests/Index', [
            'tests' => Test::with('plugin')->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Tests/Edit', ['test' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $test = Test::create($data);
        return redirect()->route('admin.tests.edit', $test);
    }

    public function edit(Test $test)
    {
        return Inertia::render('Admin/Tests/Edit', [
            'test' => $test->load('sections.questions'),
        ]);
    }

    public function update(Request $request, Test $test)
    {
        $test->update($this->validatePayload($request));
        return back()->with('success', 'Test mis à jour');
    }

    public function destroy(Test $test)
    {
        $test->delete();
        return redirect()->route('admin.tests.index');
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'scoring_engine' => ['required', 'string'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'published' => ['boolean'],
            'public' => ['boolean'],
            'gamification' => ['nullable', 'array'],
            'neuromarketing' => ['nullable', 'array'],
            'scoring_config' => ['nullable', 'array'],
        ]);
    }
}
