<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        // tests_count : nombre d'épreuves terminées du compte rattaché (0 si
        // lead sans compte). Affiché en colonne dans la liste.
        $q = Lead::query()
            ->select('leads.*')
            ->addSelect(['tests_count' => \App\Models\TestAttempt::selectRaw('count(*)')
                ->whereColumn('test_attempts.user_id', 'leads.user_id')
                ->where('status', 'completed')])
            ->latest();

        // A9 — Cloisonnement multi-tenant : les professionnels ne voient que leurs leads
        if (!auth()->user()->hasRole('admin')) {
            $accountIds = auth()->user()->professionalAccounts()->pluck('professional_accounts.id');
            $q->whereIn('professional_account_id', $accountIds);
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

        return Inertia::render('Admin/Leads/Index', [
            'leads'   => $q->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Lead $lead)
    {
        $this->authorizeLead($lead);

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
        $this->authorizeLead($lead);
        $lead->update($request->validate([
            'status' => ['required', 'in:new,contacted,qualified,converted,lost'],
            'score'  => ['nullable', 'integer', 'min:0', 'max:100'],
        ]));
        AuditLog::record('lead.updated', $lead, ['status' => $lead->status]); // #9
        return back();
    }

    public function destroy(Lead $lead)
    {
        $this->authorizeLead($lead);
        AuditLog::record('lead.destroyed', $lead, ['email' => $lead->email]); // #9
        $lead->delete();
        return back();
    }

    /** Vérifie que le professionnel accède uniquement à ses propres leads. */
    protected function authorizeLead(Lead $lead): void
    {
        if (auth()->user()->hasRole('admin')) {
            return;
        }
        $accountIds = auth()->user()->professionalAccounts()->pluck('professional_accounts.id');
        abort_unless($accountIds->contains($lead->professional_account_id), 403);
    }

    public function create() { abort(404); }
    public function store() { abort(404); }
    public function edit(Lead $lead) { return $this->show($lead); }
}
