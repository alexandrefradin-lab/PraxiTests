<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StreamsCsv;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    use StreamsCsv;

    /** Colonnes triables depuis la liste (allowlist — jamais de colonne arbitraire). */
    private const SORTABLE = ['created_at', 'email', 'first_name', 'score', 'status', 'tests_count'];

    public function index(Request $request)
    {
        $q = $this->filteredQuery($request);

        // Tri de colonnes (défaut : plus récents d'abord)
        [$sort, $dir] = $this->sortParams($request);
        $q->orderBy($sort, $dir);

        return Inertia::render('Admin/Leads/Index', [
            'leads'   => $q->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'search', 'sort', 'dir', 'trashed']),
        ]);
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        // Épreuves du compte rattaché : toutes les tentatives (terminées ou en
        // cours), hors regards d'évaluateurs 360 (rater_relation).
        // Liens vers les résultats : réservés aux admins (ResultController
        // autorise propriétaire OU admin ; les pros n'y ont pas accès).
        $isAdmin  = auth()->user()->hasRole('admin');
        $attempts = collect();
        if ($lead->user_id) {
            $attempts = \App\Models\TestAttempt::with('test:id,name,slug')
                ->where('user_id', $lead->user_id)
                ->where(fn ($q) => $q->whereNull('rater_relation')->orWhere('rater_relation', 'self'))
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($a) => [
                    'id'            => $a->id,
                    'test_name'     => $a->test?->name ?? '—',
                    'status'        => $a->status,
                    'started_at'    => $a->started_at?->format('d/m/Y H:i'),
                    'completed_at'  => $a->completed_at?->format('d/m/Y H:i'),
                    'has_synthesis' => (bool) $a->result?->ai_synthesis,
                    'results_url'   => ($isAdmin && $a->status === 'completed') ? route('results.show', $a->id, false) : null,
                    'pdf_url'       => ($isAdmin && $a->result?->ai_synthesis) ? route('results.pdf', $a->id, false) : null,
                ])
                ->values();
        }

        return Inertia::render('Admin/Leads/Show', [
            'lead'     => $lead,
            'attempts' => $attempts,
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);
        $lead->update($request->validate([
            'status' => ['required', 'in:new,contacted,qualified,converted,lost'],
            'score'  => ['nullable', 'integer', 'min:0', 'max:100'],
        ]));
        AuditLog::record('lead.updated', $lead, ['status' => $lead->status]); // #9
        return back();
    }

    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);
        AuditLog::record('lead.destroyed', $lead, ['email' => $lead->email]); // #9
        $lead->delete();
        return redirect()->route('admin.leads.index')->with('success', 'Lead placé dans la corbeille.');
    }

    /** Restaure un lead depuis la corbeille. */
    public function restore(int $id)
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        $this->authorize('restore', $lead);
        $lead->restore();
        AuditLog::record('lead.restored', $lead, ['email' => $lead->email]);
        return back()->with('success', 'Lead restauré.');
    }

    /** Export CSV de la liste courante (mêmes filtres et cloisonnement). */
    public function export(Request $request): StreamedResponse
    {
        $q = $this->filteredQuery($request)->orderByDesc('created_at');
        AuditLog::record('lead.exported', null, $request->only(['status', 'search', 'trashed']));

        return $this->streamCsv('leads-' . now()->format('Y-m-d') . '.csv', [
            'Email', 'Prénom', 'Nom', 'Téléphone', 'Source', 'Statut', 'Score', 'Épreuves terminées', 'Créé le',
        ], function () use ($q) {
            foreach ($q->lazy(500) as $lead) {
                yield [
                    $lead->email,
                    $lead->first_name,
                    $lead->last_name,
                    $lead->phone,
                    $lead->source,
                    $lead->status,
                    $lead->score,
                    $lead->tests_count ?? 0,
                    $lead->created_at?->format('d/m/Y H:i'),
                ];
            }
        });
    }

    /** Requête de liste : filtres statut/recherche/corbeille + cloisonnement tenant. */
    protected function filteredQuery(Request $request)
    {
        // tests_count : nombre d'épreuves terminées du compte rattaché (0 si
        // lead sans compte). Affiché en colonne dans la liste.
        $q = Lead::query()
            ->select('leads.*')
            ->addSelect(['tests_count' => \App\Models\TestAttempt::selectRaw('count(*)')
                ->whereColumn('test_attempts.user_id', 'leads.user_id')
                ->where('status', 'completed')]);

        // Corbeille : uniquement les éléments supprimés
        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }

        // A9 — Cloisonnement multi-tenant : les professionnels ne voient que leurs leads
        if (!auth()->user()->hasRole('admin')) {
            $q->whereIn('professional_account_id', auth()->user()->professionalAccountIds() ?: [0]);
        }

        if ($request->filled('status')) {
            $validStatuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
            $statusFilter = $request->string('status')->toString();
            if (in_array($statusFilter, $validStatuses, true)) {
                $q->where('status', $statusFilter);
            }
        }
        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(fn ($x) => $x->where('email', 'like', "%{$s}%")->orWhere('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"));
        }

        return $q;
    }

    /** Tri demandé, restreint à l'allowlist. */
    protected function sortParams(Request $request): array
    {
        $sort = $request->string('sort')->toString();
        $dir  = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';
        if (!in_array($sort, self::SORTABLE, true)) {
            $sort = 'created_at';
        }
        return [$sort, $dir];
    }

    public function create() { abort(404); }
    public function store() { abort(404); }
    public function edit(Lead $lead) { return $this->show($lead); }
}
