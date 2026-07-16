<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignJob;
use App\Models\AuditLog;
use App\Models\EmailCampaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    use \App\Http\Controllers\Concerns\SortsColumns;

    /** Colonnes triables depuis la liste (allowlist). */
    private const SORTABLE = ['name', 'subject', 'status', 'sent_at', 'created_at'];

    public function index(Request $request)
    {
        // A10 — Cloisonnement multi-tenant : les professionnels ne voient que leurs campagnes
        $q = EmailCampaign::query();

        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }

        if (!auth()->user()->hasRole('admin')) {
            $q->whereIn('professional_account_id', auth()->user()->professionalAccountIds() ?: [0]);
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if (in_array($status, ['draft', 'scheduled', 'sending', 'sent', 'partial', 'failed', 'paused'], true)) {
                $q->where('status', $status);
            }
        }
        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(fn ($x) => $x->where('name', 'like', "%{$s}%")->orWhere('subject', 'like', "%{$s}%"));
        }

        [$sort, $dir] = $this->sortParams($request, self::SORTABLE);

        $campaigns = $q->orderBy($sort, $dir)->paginate(25)->withQueryString()
            // Stats d'envoi visibles dès la liste (délivrés / ouverts / cliqués)
            ->through(fn (EmailCampaign $c) => [
                'id'           => $c->id,
                'name'         => $c->name,
                'subject'      => $c->subject,
                'status'       => $c->status,
                'scheduled_at' => $c->scheduled_at?->format('d/m/Y H:i'),
                'sent_at'      => $c->sent_at?->format('d/m/Y H:i'),
                'delivered'    => (int) ($c->stats['delivered'] ?? 0),
                'opened'       => (int) ($c->stats['opened'] ?? 0),
                'clicked'      => (int) ($c->stats['clicked'] ?? 0),
                'deleted_at'   => $c->deleted_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters'   => $request->only(['status', 'search', 'trashed', 'sort', 'dir']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Campaigns/Edit', ['campaign' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        // Rattachement tenant : première PA du professionnel (null pour un admin).
        $user = auth()->user();
        $data['professional_account_id'] = $user->hasRole('admin')
            ? null
            : ($user->professionalAccountIds()[0] ?? null);

        $campaign = EmailCampaign::create(array_merge($data, ['status' => 'draft']));
        AuditLog::record('campaign.created', $campaign, ['name' => $campaign->name]); // #9
        return redirect()->route('admin.campaigns.edit', $campaign);
    }

    public function edit(EmailCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        return Inertia::render('Admin/Campaigns/Edit', ['campaign' => $campaign]);
    }

    public function update(Request $request, EmailCampaign $campaign)
    {
        $this->authorize('update', $campaign);
        $campaign->update($this->validatePayload($request));
        AuditLog::record('campaign.updated', $campaign, ['name' => $campaign->name]); // #9 — manquait
        return back()->with('success', 'Campagne mise à jour');
    }

    public function destroy(EmailCampaign $campaign)
    {
        $this->authorize('delete', $campaign);
        AuditLog::record('campaign.destroyed', $campaign, ['name' => $campaign->name]); // #9
        $campaign->delete(); // corbeille (soft delete) — restaurable
        return redirect()->route('admin.campaigns.index')->with('success', 'Campagne placée dans la corbeille.');
    }

    /** Restaure une campagne depuis la corbeille. */
    public function restore(int $id)
    {
        $campaign = EmailCampaign::withTrashed()->findOrFail($id);
        $this->authorize('restore', $campaign);
        $campaign->restore();
        AuditLog::record('campaign.restored', $campaign, ['name' => $campaign->name]);
        return back()->with('success', 'Campagne restaurée.');
    }

    public function send(EmailCampaign $campaign)
    {
        $this->authorize('send', $campaign);

        // T3 — l'envoi sort du cycle HTTP. Statut 'sending' immédiat ; le job
        // (CampaignService) le clôturera en sent/partial/failed. Sur queue sync
        // le job tourne en ligne (identique à l'ancien comportement).
        $campaign->update(['status' => 'sending']);

        SendCampaignJob::dispatch($campaign->id);

        AuditLog::record('campaign.sent', $campaign, []); // #9
        return back()->with('success', 'Envoi de la campagne lancé.');
    }

    protected function validatePayload(Request $request): array
    {
        // Les casts array du modèle EmailCampaign gèrent l'encodage JSON —
        // plus de json_encode manuel (source de doubles encodages).
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:200'],
            'preheader' => ['nullable', 'string', 'max:200'],
            'body_html' => ['required', 'string'],
            'body_text' => ['nullable', 'string'],
            'audience_filter' => ['nullable', 'array'],
            'variants' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
        ]);
    }
}
