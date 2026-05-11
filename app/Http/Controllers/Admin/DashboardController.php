<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\TestAttempt;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'        => User::count(),
            'attempts_completed' => TestAttempt::where('status', 'completed')->count(),
            'attempts_inprogress' => TestAttempt::where('status', 'in_progress')->count(),
            'completion_rate'    => $this->completionRate(),
            'leads_new'          => Lead::where('status', 'new')->count(),
            'leads_qualified'    => Lead::where('status', 'qualified')->count(),
        ];

        $recent_attempts = TestAttempt::with('user', 'test')
            ->latest('completed_at')
            ->limit(10)
            ->get();

        $recent_leads = Lead::latest()->limit(10)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_attempts' => $recent_attempts,
            'recent_leads'    => $recent_leads,
        ]);
    }

    protected function completionRate(): float
    {
        $started = TestAttempt::count();
        if (!$started) return 0;
        $done = TestAttempt::where('status', 'completed')->count();
        return round(($done / $started) * 100, 1);
    }
}
