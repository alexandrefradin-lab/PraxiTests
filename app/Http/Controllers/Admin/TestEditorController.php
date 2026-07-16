<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Praxis\Core\TestEngine\TestEngine;

class TestEditorController extends Controller
{
    use \App\Http\Controllers\Concerns\SortsColumns;

    /** Colonnes triables depuis la liste (allowlist). */
    private const SORTABLE = ['name', 'type', 'estimated_minutes', 'published', 'created_at'];

    public function __construct(protected TestEngine $engine) {}

    public function index(Request $request)
    {
        $q = Test::with('plugin');

        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }

        if ($request->filled('published')) {
            $q->where('published', $request->string('published')->toString() === 'yes');
        }

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(fn ($x) => $x->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"));
        }

        [$sort, $dir] = $this->sortParams($request, self::SORTABLE);

        return Inertia::render('Admin/Tests/Index', [
            'tests'   => $q->orderBy($sort, $dir)->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'published', 'trashed', 'sort', 'dir']),
        ]);
    }

    /** Restaure un test depuis la corbeille. */
    public function restore(int $id)
    {
        $test = Test::withTrashed()->findOrFail($id);
        $test->restore();
        AuditLog::record('test.restored', $test, ['slug' => $test->slug]);
        return back()->with('success', "Test « {$test->name} » restauré.");
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
        $data = $this->validatePayload($request, null);
        $test = Test::create($data);
        AuditLog::record('test.created', $test, ['slug' => $test->slug, 'name' => $test->name]); // #9
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
        $test->update($this->validatePayload($request, $test));
        AuditLog::record('test.updated', $test, ['slug' => $test->slug]); // #9
        return back()->with('success', 'Test mis à jour');
    }

    public function destroy(Test $test)
    {
        AuditLog::record('test.destroyed', $test, ['slug' => $test->slug, 'name' => $test->name]); // #9
        $test->delete();
        return redirect()->route('admin.tests.index')->with('success', 'Test placé dans la corbeille.');
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
            'sections.*.questions.*.type'       => ['required', 'string', 'in:scale,text,multi,ranking,single,situational,exercise'],
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

                    // A7 — options/scoring peuvent arriver en string JSON (textarea) ou déjà décodés.
                    // Si c'est une string non vide, on valide le JSON explicitement.
                    $options = $qData['options'] ?? null;
                    if (is_string($options) && $options !== '') {
                        $decoded = json_decode($options, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'options' => 'Le champ options contient un JSON invalide : ' . json_last_error_msg(),
                            ]);
                        }
                        $options = $decoded;
                    } elseif ($options === '') {
                        $options = null;
                    }

                    $scoring = $qData['scoring'] ?? null;
                    if (is_string($scoring) && $scoring !== '') {
                        $decoded = json_decode($scoring, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'scoring' => 'Le champ scoring contient un JSON invalide : ' . json_last_error_msg(),
                            ]);
                        }
                        $scoring = $decoded;
                    } elseif ($scoring === '') {
                        $scoring = null;
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

        AuditLog::record('test.structure_saved', $test, [ // #9
            'sections_count' => count($data['sections']),
        ]);

        return back()->with('success', 'Structure sauvegardée — ' . count($data['sections']) . ' section(s)');
    }

    protected function validatePayload(Request $request, ?Test $test = null): array
    {
        // A8 — Unicité du slug en excluant le test courant lors d'un update
        $slugRule = $test
            ? Rule::unique('tests', 'slug')->ignore($test->id)
            : Rule::unique('tests', 'slug');

        return $request->validate([
            'slug'              => ['required', 'string', 'max:120', $slugRule],
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
