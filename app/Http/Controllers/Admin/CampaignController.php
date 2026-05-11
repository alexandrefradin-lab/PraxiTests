<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Praxis\Core\Mailing\Services\CampaignService;

class CampaignController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => DB::table('email_campaigns')->latest()->get(),
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
        return redirect()->route('admin.campaigns.edit', $id);
    }

    public function edit($id)
    {
        return Inertia::render('Admin/Campaigns/Edit', [
            'campaign' => DB::table('email_campaigns')->find($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::table('email_campaigns')->where('id', $id)->update(array_merge(
            $this->validatePayload($request),
            ['updated_at' => now()],
        ));
        return back()->with('success', 'Campagne mise à jour');
    }

    public function destroy($id)
    {
        DB::table('email_campaigns')->where('id', $id)->delete();
        return redirect()->route('admin.campaigns.index');
    }

    public function send($id, CampaignService $svc)
    {
        $stats = $svc->sendCampaign($id);
        return back()->with('success', "Campagne envoyée — {$stats['queued']} mails en file");
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
