<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('admin.dashboard.stats', 60, function () {
            $attempts = \App\Models\TestAttempt::selectRaw("
                COUNT(*) as total,
                SUM(status = 'completed') as completed,
                SUM(status = 'in_progress') as in_progress
            ")->first();

            $leads = \App\Models\Lead::selectRaw("
                SUM(status = 'new') as new_leads,
                SUM(status = 'qualified') as qualified_leads
            ")->first();

            return [
                'total_users'         => \App\Models\User::count(),
                'attempts_completed'  => (int) ($attempts->completed ?? 0),
                'attempts_inprogress' => (int) ($attempts->in_progress ?? 0),
                'completion_rate'     => ($attempts->total ?? 0) > 0
                    ? round(($attempts->completed / $attempts->total) * 100, 1) : 0,
                'leads_new'           => (int) ($leads->new_leads ?? 0),
                'leads_qualified'     => (int) ($leads->qualified_leads ?? 0),
            ];
        });

        $recent_atte