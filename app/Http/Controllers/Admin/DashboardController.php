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
            ? TestAttempt::with('user:id,name,email', 'test:id,name,slug')
                ->latest('completed_at')->limit(10)->get()
                ->map(fn ($a) => [
                    'id'           => $a->id,
                    'user'         => $a->user ? ['name' => $a->user->name, 'email' => $a->user->email] : null,
                    'test'         => $a->test ? ['name' => $a->test->name] : null,
                    'completed_at' => $a->completed_at?->diffForHumans(),
                    // Lien vers le résultat (admin uniquement — même règle que LeadController::show)
                    'results_url'  => $a->status === 'completed' ? route('results.show', $a->id, false) : null,
                ])
            : collect();

        $recent_leads = Lead::with(['user', 'professionalAccount'])
            ->when(! $isAdmin, fn ($q) => $q->whereIn('professional_account_id', $accountIds ?: [0]))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($l) => [
                'id'         => $l->id,
                'first_name' => $l->first_name,
                'last_name'  => $l->last_name,
                'email'      => $l->email,
                'status'     => $l->status,
                'created_at' => $l->created_at?->diffForHumans(),
            ]);

        // ---- Activité 14 jours : tests complétés par jour (admin) ----
        $activity = [];
        if ($isAdmin) {
            $byDay = TestAttempt::query()
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->where('completed_at', '>=', now()->subDays(13)->startOfDay())
                ->selectRaw('DATE(completed_at) as d, COUNT(*) as c')
                ->groupBy('d')
                ->pluck('c', 'd');

            for ($i = 13; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $activity[] = [
                    'label' => $day->format('d/m'),
                    'value' => (int) ($byDay[$day->format('Y-m-d')] ?? 0),
                ];
            }
        }

        // ---- Alertes exploitation : à traiter aujourd'hui ----
        $alerts = [];
        if ($isAdmin) {
            $failedInsights = \App\Models\TestResult::where('ai_failed', true)->count();
            if ($failedInsights > 0) {
                $alerts[] = [
                    'label' => "{$failedInsights} synthèse(s) IA en échec",
                    'href'  => route('admin.attempts.failed-insights', absolute: false),
                ];
            }
        }
        $expiredInvitations = \App\Models\TestInvitation::query()
            ->when(! $isAdmin, fn ($q) => $q->whereIn('professional_account_id', $accountIds ?: [0]))
            ->where('expires_at', '<', now())
            ->whereNotIn('status', ['completed'])
            ->count();
        if ($expiredInvitations > 0) {
            $alerts[] = [
                'label' => "{$expiredInvitations} invitation(s) expirée(s) sans réponse",
                'href'  => route('admin.invitations.index', ['status' => 'expired'], false),
            ];
        }

        return Inertia::render('Admin/Dashboard', [
            'stats'           => $stats,
            'recent_attempts' => $recent_attempts,
            'recent_leads'    => $recent_leads,
            'activity'        => $activity,
            'alerts'          => $alerts,
        ]);
    }
}
