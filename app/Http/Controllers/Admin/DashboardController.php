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
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Cloisonnement multi-tenant (audit risque #4) : les professionnels
        // ne voient que les données liées à leurs comptes.
        $accountIds = $isAdmin
            ? null
            : $user->professionalAccounts()->pluck('professional_accounts.id')->all();

        // Les stats globales (users, attempts) ne sont visibles que par l'admin.
        // Les pros voient uniquement leurs leads et candidats.
        $cacheKey = $isAdmin ? 'admin.dashboard.stats' : 'pro.dashboard.stats.' . $user->id;

        $stats = Cache::remember($cacheKey, 60, function () use ($isAdmin, $accountIds) {
            if ($isAdmin) {
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
            }

            // Pro : stats restreintes à ses comptes
            $leads = \App\Models\Lead::whereIn('professional_account_id', $accountIds ?: [0])
                ->selectRaw("SUM(status = 'new') as new_leads, SUM(status = 'qualified') as qualified_leads")
                ->first();

            return [
                'total_users'         => null,
                'attempts_completed'  => null,
                'attempts_inprogress' => null,
                'completion_rate'     => null,
                'leads_new'           => (int) ($leads->new_leads ?? 0),
                'leads_qualified'     => (int) ($leads->qualified_leads ?? 0),
            ];
        });

        $recent_attempts = $isAdmin
            ? TestAttempt::with('user', 'test')->latest('completed_at')->limit(10)->get()
            : collect();

        $recent_leads = Lead::with(['user', 'professionalAccount'])
            ->when(! $isAdmin, fn ($q) => $q->whereIn('professional_account_id', $accountIds ?: [0]))
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats'           => $stats,
            'recent_attempts' => $recent_attempts,
            'recent_leads'    => $recent_leads,
        ]);
    }
}
