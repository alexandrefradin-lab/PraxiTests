<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignJob;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function index()
    {
        // A10 — Cloisonnement multi-tenant : les professionnels ne voient que leurs campagnes
        $query = DB::table('email_campaigns')->latest();
        if (!auth()->user()->hasRole('admin')) {
            $accountIds = auth()->user()->professionalAccounts()->pluck('professional_accounts.id');
            $query->whereIn('professional_account_id', $accountIds);
        }

        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => $query->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Campaigns/Edit', ['campaign' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $id = DB::table('email_campaigns')->insertGetId(array_merge($data, [
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        AuditLog::record('campaign.created', null, ['id' => $id, 'name' => $data['name']]); // #9
        return redirect()->route('admin.campaigns.edit', $id);
    }

    public function edit($id)
    {
        $campaign = $this->findAndAuthorizeCampaign($id);
        return Inertia::render('Admin/Campaigns/Edit', ['campaign' => $campaign]);
    }

    public function update(Request $request, $id)
    {
        $this->findAndAuthorizeCampaign($id);
        DB::table('email_campaigns')->where('id', $id)->update(array_merge(
            $this->validatePayload($request),
            ['updated_at' => now()],
        ));
        return back()->with('success', 'Campagne mise à jour');
    }

    public function destroy($id)
    {
        $campaign = $this->findAndAuthorizeCampaign($id);
        AuditLog::record('campaign.destroyed', null, ['id' => $id, 'name' => $campaign->name]); // #9
        DB::table('email_campaigns')->where('id', $id)->delete();
        return redirect()->route('admin.campaigns.index');
    }

    public function send($id)
    {
        $this->findAndAuthorizeCampaign($id);

        // T3 — l'envoi sort du cycle HTTP. Statut 'sending' immédiat ; le job
        // (CampaignService) le clôturera en sent/partial/failed. Sur queue sync
        // le job tourne en ligne (identique à l'ancien comportement).
        DB::table('email_campaigns')->where('id', $id)->update([
            'status'     => 'sending',
            'updated_at' => now(),
        ]);

        SendCampaignJob::dispatch((int) $id);

        AuditLog::record('campaign.sent', null, ['id' => $id]); // #9
        return back()->with('success', "Envoi de la campagne lancé.");
    }

    /** Récupère la campagne et vérifie le cloisonnement tenant (A10). */
    protected function findAndAuthorizeCampaign(int|string $id): object
    {
        $campaign = DB::table('email_campaigns')->find($id);
        abort_if($campaign === null, 404);

        if (!auth()->user()->hasRole('admin')) {
            $accountIds = auth()->user()->professionalAccounts()->pluck('professional_accounts.id');
            abort_unless($accountIds->contains($campaign->professional_account_id), 403);
        }

        return $campaign;
    }

    protected function validatePayload(Request $request): array
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:200'],
            'preheader' => ['nullable', 'string', 'max:200'],
            'body_html' => ['required', 'string'],
            'body_text' => ['nullable', 'string'],
            'audience_filter' => ['nullable', 'array'],
            'variants' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
        ]);
        $v['audience_filter'] = json_encode($v['audience_filter'] ?? []);
        $v['variants'] = json_encode($v['variants'] ?? []);
        return $v;
    }
}
