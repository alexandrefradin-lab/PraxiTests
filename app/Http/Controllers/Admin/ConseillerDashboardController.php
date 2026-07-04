<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use App\Models\TestInvitation;
use App\Models\TestResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Tableau de bord Conseiller.
 *
 * Vue dédiée au rôle "professional" (conseiller / cabinet) : suivi des
 * candidats invités, synthèses IA + idées de métiers, campagnes d'invitation
 * et indicateurs clés. Les administrateurs voient l'ensemble des comptes,
 * les conseillers uniquement leur(s) compte(s) professionnel(s)
 * (cloisonnement multi-tenant — cohérent avec Campaign/Lead controllers).
 */
class ConseillerDashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Comptes professionnels accessibles. null => admin (pas de filtre).
        $accountIds = $isAdmin
            ? null
            : ($user->professionalAccounts()->pluck('professional_accounts.id')->all() ?: [0]);

        $scopeInvitations = fn ($q) => $isAdmin ? $q : $q->whereIn('professional_account_id', $accountIds);

        // ---- KPIs : invitations (candidats) ----
        $invStats = TestInvitation::query()
            ->tap($scopeInvitations)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'completed') as completed,
                SUM(status = 'started')   as started,
                SUM(status IN ('pending','sent','opened')) as waiting
            ")->first();

        $invited   = (int) ($invStats->total ?? 0);
        $completed = (int) ($invStats->completed ?? 0);
        $started   = (int) ($invStats->started ?? 0);
        $waiting   = (int) ($invStats->waiting ?? 0);

        // ---- Synthèses IA & métiers (results -> attempts -> invitations) ----
        // Fabrique de requête fraîche : on évite `clone` qui partagerait la
        // requête sous-jacente entre deux usages (piège Eloquent Builder).
        $resultsQuery = fn () => TestResult::query()
            ->join('test_attempts', 'test_results.attempt_id', '=', 'test_attempts.id')
            ->leftJoin('test_invitations', 'test_attempts.invitation_id', '=', 'test_invitations.id')
            ->when(! $isAdmin, fn ($q) => $q->whereIn('test_invitations.professional_account_id', $accountIds));

        $aiSyntheses = $resultsQuery()->whereNotNull('test_results.ai_synthesis')->count();

        $stats = [
            'invited'         => $invited,
            'completed'       => $completed,
            'in_progress'     => $started,
            'waiting'         => $waiting,
            'completion_rate' => $invited > 0 ? round($completed / $invited * 100, 1) : 0,
            'ai_syntheses'    => $aiSyntheses,
        ];

        // ---- Liste des candidats (invitation + tentative + résultat) ----
        $invitations = TestInvitation::query()
            ->tap($scopeInvitations)
            ->with('test:id,name,slug')
            ->latest()
            ->limit(40)
            ->get();

        $attempts = TestAttempt::query()
            ->whereIn('invitation_id', $invitations->pluck('id')->filter()->all() ?: [0])
            ->with('result:id,attempt_id,ai_synthesis,suggested_jobs')
            ->get()
            ->keyBy('invitation_id');

        $candidates = $invitations->map(function (TestInvitation $inv) use ($attempts) {
            $attempt = $attempts->get($inv->id);
            $jobs    = $this->decodeJson($attempt?->result?->suggested_jobs);

            return [
                'id'         => $inv->id,
                'name'       => trim(($inv->first_name ?? '') . ' ' . ($inv->last_name ?? '')) ?: '—',
                'email'      => $inv->email,
                'test'       => $inv->test?->name,
                'status'     => $inv->status,
                'sent_at'    => optional($inv->sent_at)->format('d/m/Y'),
                'progress'   => $attempt ? $this->avgProgress($attempt->progress) : 0,
                'has_ai'     => (bool) ($attempt?->result?->ai_synthesis),
                'jobs_count' => is_array($jobs) ? count($jobs) : 0,
                'attempt_id' => $attempt?->id,
            ];
        })->values();

        // ---- Panneau synthèses IA & idées de métiers ----
        // SEC-M12 / RGPD : un professionnel ne voit le CONTENU des synthèses que
        // si le candidat a consenti au partage (consent_share_professional,
        // capturé à l'inscription via invitation). Les admins ne sont pas filtrés.
        // Migration 2026_06_25_100010 appliquée — le TODO est levé.
        $aiInsights = $resultsQuery()
            ->when(! $isAdmin, fn ($q) => $q->where('test_invitations.consent_share_professional', true))
            ->whereNotNull('test_results.ai_synthesis')
            ->orderByDesc('test_results.id')
            ->limit(8)
            ->get([
                'test_results.id as result_id',
                'test_results.ai_synthesis',
                'test_results.suggested_jobs',
                'test_attempts.id as attempt_id',
                'test_invitations.email as email',
                'test_invitations.first_name',
                'test_invitations.last_name',
            ])
            ->map(function ($r) {
                $jobs    = $this->decodeJson($r->suggested_jobs) ?: [];
                $topJobs = collect($jobs)->take(3)->map(function ($j) {
                    if (is_array($j)) {
                        return $j['title'] ?? $j['name'] ?? $j['metier'] ?? 'Métier';
                    }
                    return (string) $j;
                })->all();

                return [
                    'attempt_id' => $r->attempt_id,
                    'candidate'  => trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: ($r->email ?? 'Anonyme'),
                    'excerpt'    => Str::limit(trim(strip_tags((string) $r->ai_synthesis)), 150),
                    'jobs_count' => count($jobs),
                    'top_jobs'   => $topJobs,
                ];
            })->values();

        // ---- Campagnes d'invitation ----
        $campaigns = DB::table('email_campaigns')
            ->when(! $isAdmin, fn ($q) => $q->whereIn('professional_account_id', $accountIds))
            ->latest()
            ->limit(8)
            ->get()
            ->map(function ($c) {
                $s = $this->decodeJson($c->stats ?? null) ?: [];

                return [
                    'id'        => $c->id,
                    'name'      => $c->name,
                    'status'    => $c->status,
                    'sent_at'   => $c->sent_at ? \Illuminate\Support\Carbon::parse($c->sent_at)->format('d/m/Y') : null,
                    'delivered' => (int) ($s['delivered'] ?? 0),
                    'opened'    => (int) ($s['opened'] ?? 0),
                    'clicked'   => (int) ($s['clicked'] ?? 0),
                ];
            })->values();

        // ---- Graphique : entonnoir candidats ----
        $funnel = [
            ['label' => 'Invités',    'value' => $invited],
            ['label' => 'En attente', 'value' => $waiting],
            ['label' => 'En cours',   'value' => $started],
            ['label' => 'Terminés',   'value' => $completed],
        ];

        // ---- Graphique : tests complétés sur 14 jours ----
        $byDay = TestAttempt::query()
            ->when(! $isAdmin, fn ($q) => $q->whereHas('invitation', fn ($i) => $i->whereIn('professional_account_id', $accountIds)))
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(completed_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $activity = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $activity[] = [
                'label' => $day->format('d/m'),
                'value' => (int) ($byDay[$day->format('Y-m-d')] ?? 0),
            ];
        }

        return Inertia::render('Admin/ConseillerDashboard', [
            'stats'      => $stats,
            'candidates' => $candidates,
            'aiInsights' => $aiInsights,
            'campaigns'  => $campaigns,
            'funnel'     => $funnel,
            'activity'   => $activity,
        ]);
    }

    /** Décode une valeur JSON éventuellement déjà castée en tableau. */
    private function decodeJson($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    /** Moyenne (%) d'avancement à partir du tableau progress (section => %). */
    private function avgProgress($progress): int
    {
        $progress = $this->decodeJson($progress);
        if (empty($progress)) {
            return 0;
        }
        $vals = array_filter($progress, 'is_numeric');
        if (empty($vals)) {
            return 0;
        }
        return (int) round(array_sum($vals) / count($vals));
    }
}
