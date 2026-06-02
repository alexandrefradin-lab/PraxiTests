<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Praxis\Core\TestEngine\TestEngine;

class TestEditorController extends Controller
{
    public function __construct(protected TestEngine $engine) {}
    public function index()
    {
        return Inertia::render('Admin/Tests/Index', [
            'tests' => Test::with('plugin')->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Tests/Edit', [
            'test'            => null,
            'scoring_engines' => $this->engine->availableEngines(),
        ]);
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
            'test'            => $test->load('sections.questions'),
            'scoring_engines' => $this->engine->availableEngines(),
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

    /**
     * Sauvegarde la structure complète (sections + questions) en une transaction.
     * Le frontend envoie l'arbre entier ; on upsert et on supprime ce qui a disparu.
     */
    public function saveStructure(Request $request, Test $test)
    {
        $data = $request->validate([
            'sections'                          => ['required', 'array'],
            'sections.*.id'                     => ['nullable', 'integer'],
            'sections.*.title'                  => ['required', 'string', 'max:200'],
            'sections.*.description'            => ['nullable', 'string'],
            'sections.*.narrative_intro'        => ['nullable', 'string'],
            'sections.*.order'                  => ['required', 'integer', 'min:0'],
            'sections.*.questions'              => ['array'],
            'sections.*.questions.*.id'         => ['nullable', 'integer'],
            'sections.*.questions.*.type'       => ['required', 'string'],
            'sections.*.questions.*.prompt'     => ['required', 'string'],
            'sections.*.questions.*.helper'     => ['nullable', 'string'],
            'sections.*.questions.*.order'      => ['required', 'integer', 'min:0'],
            'sections.*.questions.*.options'    => ['nullable'],
            'sections.*.questions.*.scoring'    => ['nullable'],
            'sections.*.questions.*.required'   => ['boolean'],
        ]);

        DB::transaction(function () use ($test, $data) {
            $keptSectionIds = [];

            foreach ($data['sections'] as $sData) {
                $section = (isset($sData['id']) && $sData['id'])
                    ? TestSection::where('test_id', $test->id)->findOrFail((int) $sData['id'])
                    : new TestSection(['test_id' => $test->id]);

                $section->fill([
                    'title'           => $sData['title'],
                    'description'     => $sData['description'] ?? null,
                    'narrative_intro' => $sData['narrative_intro'] ?? null,
                    'order'           => (int) $sData['order'],
                ])->save();

                $keptSectionIds[] = $section->id;
                $keptQIds = [];

                foreach ($sData['questions'] ?? [] as $qData) {
                    $question = (isset($qData['id']) && $qData['id'])
                        ? TestQuestion::where('section_id', $section->id)->findOrFail((int) $qData['id'])
                        : new TestQuestion(['section_id' => $section->id]);

                    // options peut arriver en string JSON (textarea) ou déjà décodé
                    $options = $qData['options'] ?? null;
                    if (is_string($options)) {
                        $options = json_decode($options, true) ?: null;
                    }
                    $scoring = $qData['scoring'] ?? null;
                    if (is_string($scoring)) {
                        $scoring = json_decode($scoring, true) ?: null;
                    }

                    $question->fill([
                        'type'     => $qData['type'],
                        'prompt'   => $qData['prompt'],
                        'helper'   => $qData['helper'] ?? null,
                        'order'    => (int) $qData['order'],
                        'options'  => $options,
                        'scoring'  => $scoring,
                        'required' => (bool) ($qData['required'] ?? true),
                    ])->save();

                    $keptQIds[] = $question->id;
                }

                // Supprimer les questions retirées de cette section
                if ($keptQIds) {
                    TestQuestion::where('section_id', $section->id)->whereNotIn('id', $keptQIds)->delete();
                } else {
                    TestQuestion::where('section_id', $section->id)->delete();
                }
            }

            // Supprimer les sections retirées (cascade supprime leurs questions)
            if ($keptSectionIds) {
                TestSection::where('test_id', $test->id)->whereNotIn('id', $keptSectionIds)->delete();
            } else {
                TestSection::where('test_id', $test->id)->delete();
            }
        });

        return back()->with('success', 'Structure sauvegardée — ' . count($data['sections']) . ' section(s)');
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'slug'              => ['required', 'string', 'max:120'],
            'name'              => ['required', 'string', 'max:200'],
            'description'       => ['nullable', 'string'],
            'type'              => ['required', 'string'],
            'scoring_engine'    => ['required', 'string'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'published'         => ['boolean'],
            'public'            => ['boolean'],
            'gamification'      => ['nullable', 'array'],
            'neuromarketing'    => ['nullable', 'array'],
            'scoring_config'    => ['nullable', 'array'],
        ]);
    }
}
