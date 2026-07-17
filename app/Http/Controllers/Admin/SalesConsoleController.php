<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StreamsCsv;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Laravel\Cashier\Subscription;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Console superadmin des ventes : vue consolidée du revenu (MRR/ARR),
 * segmentée particuliers (indépendants) vs cabinets (ProfessionalAccount).
 *
 * NB : la table Cashier v15 utilise la colonne `type` (et non `name`)
 * pour identifier l'abonnement — toujours 'default' ici.
 */
class SalesConsoleController extends Controller
{
    use StreamsCsv;

    public function index(Request $request)
    {
        $plans          = config('plans.plans');
        $priceMap       = $this->buildPriceMap($plans);
        $cabinetUserIds = $this->cabinetUserIds();
        $search         = $request->string('search')->toString();
        $filterPlan     = $request->get('plan');
        $filterStatus   = $request->get('status'); // active | trialing | cancelled
        $subFilter      = $this->subscriptionFilter($plans, $filterPlan, $filterStatus);
        $hasSubFilter   = ($filterPlan && isset($plans[$filterPlan])) || in_array($filterStatus, ['active', 'trialing', 'cancelled'], true);

        // ── KPIs abonnements (agrégats SQL, rien en mémoire) ──────────────────
        // « Actif » = payant : hors essai et hors résiliation en cours.
        $activeCount    = $this->baseQuery()->active()->notOnGracePeriod()->notOnTrial()->count();
        $trialCount     = $this->baseQuery()->onTrial()->count();
        $graceCount     = $this->baseQuery()->canceled()->onGracePeriod()->count();
        $cancelledCount = $this->baseQuery()->canceled()->notOnGracePeriod()->count();

        // ── MRR global + segmenté ──────────────────────────────────────────────
        $mrrTotal    = $this->computeMrr($priceMap);
        $mrrCabinets = $cabinetUserIds
            ? $this->computeMrr($priceMap, fn ($q) => $q->whereIn('user_id', $cabinetUserIds))
            : 0;
        $mrrParticuliers = $mrrTotal - $mrrCabinets;

        // ── Nouveaux abonnés : mois en cours vs mois précédent ─────────────────
        $newThisMonth = $this->baseQuery()->where('created_at', '>=', now()->startOfMonth())->count();
        $newLastMonth = $this->baseQuery()->whereBetween('created_at', [
            now()->startOfMonth()->subMonthNoOverflow(),
            now()->startOfMonth(),
        ])->count();

        // ── Répartition par plan (abonnements payants, cohérent avec le MRR) ──
        $activeByPrice = $this->baseQuery()->active()->notOnGracePeriod()->notOnTrial()
            ->selectRaw('stripe_price, COUNT(*) as c')
            ->groupBy('stripe_price')
            ->pluck('c', 'stripe_price');

        $planBreakdown = collect($plans)->map(function ($plan, $key) use ($activeByPrice, $priceMap) {
            $count = 0;
            $mrr   = 0;
            foreach (['stripe_monthly', 'stripe_yearly'] as $field) {
                $priceId = $plan[$field] ?? '';
                if ($priceId !== '' && isset($activeByPrice[$priceId])) {
                    $count += $activeByPrice[$priceId];
                    $mrr   += ($priceMap[$priceId] ?? 0) * $activeByPrice[$priceId];
                }
            }
            return [
                'key'       => $key,
                'name'      => $plan['name'],
                'color'     => $plan['color'],
                'available' => $plan['available'],
                'count'     => $count,
                'mrr'       => $mrr,
            ];
        })->values();

        // ── Tendance 12 mois : nouveaux abonnements + résiliations ─────────────
        // Groupement en PHP (volumes faibles) : portable MySQL/SQLite.
        $trend = $this->buildTrend($priceMap, $cabinetUserIds);

        // ── Onglet Particuliers : abonnés hors cabinet ─────────────────────────
        $particuliers = User::query()
            ->with(['subscriptions' => fn ($q) => $q->where('type', 'default')->latest()])
            ->whereHas('subscriptions', $subFilter)
            ->when($cabinetUserIds, fn ($q) => $q->whereNotIn('id', $cabinetUserIds))
            ->when($search !== '', fn ($q) => $q->where(fn ($x) => $x
                ->where('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")))
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'pp')
            ->withQueryString()
            ->through(fn ($u) => $this->presentUser($u, $plans, $priceMap));

        // ── Onglet Cabinets : comptes pro + facturation du propriétaire ────────
        // Sans filtre abonnement, on montre TOUS les cabinets (y compris le
        // pipeline en trial, sans abonnement Stripe). Avec filtre, on restreint
        // aux cabinets dont le propriétaire a un abonnement correspondant.
        $cabinets = ProfessionalAccount::query()
            ->withCount('members')
            ->with(['owner.subscriptions' => fn ($q) => $q->where('type', 'default')->latest()])
            ->when($hasSubFilter, fn ($q) => $q->whereHas('owner', fn ($o) => $o->whereHas('subscriptions', $subFilter)))
            ->when($search !== '', fn ($q) => $q->where(fn ($x) => $x
                ->where('company_name', 'like', "%{$search}%")
                ->orWhereHas('owner', fn ($o) => $o
                    ->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%"))))
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'pc')
            ->withQueryString()
            ->through(fn ($a) => $this->presentAccount($a, $plans, $priceMap));

        return Inertia::render('Admin/Sales/Index', [
            'kpis' => [
                'mrr'              => $mrrTotal,
                'arr'              => $mrrTotal * 12,
                'mrr_particuliers' => $mrrParticuliers,
                'mrr_cabinets'     => $mrrCabinets,
                'active'           => $activeCount,
                'trialing'         => $trialCount,
                'grace'            => $graceCount,
                'cancelled'        => $cancelledCount,
                'new_this_month'   => $newThisMonth,
                'new_last_month'   => $newLastMonth,
                'cabinets_total'   => ProfessionalAccount::count(),
            ],
            'plan_breakdown' => $planBreakdown,
            'trend'          => $trend,
            'particuliers'   => $particuliers,
            'cabinets'       => $cabinets,
            'plans'          => collect($plans)->map(fn ($p, $k) => ['key' => $k, 'name' => $p['name']])->values(),
            'filters'        => ['search' => $search, 'plan' => $filterPlan, 'status' => $filterStatus],
        ]);
    }

    /**
     * Export CSV par segment (?segment=particuliers|cabinets).
     * Parcours lazy — pas de pagination, sobre en mémoire.
     */
    public function export(Request $request): StreamedResponse
    {
        $segment  = $request->get('segment') === 'cabinets' ? 'cabinets' : 'particuliers';
        $plans    = config('plans.plans');
        $priceMap = $this->buildPriceMap($plans);

        \App\Models\AuditLog::record('sales.exported', null, ['segment' => $segment]);

        if ($segment === 'cabinets') {
            $q = ProfessionalAccount::query()
                ->withCount('members')
                ->with(['owner.subscriptions' => fn ($s) => $s->where('type', 'default')->latest()])
                ->orderByDesc('created_at');

            return $this->streamCsv('ventes-cabinets-' . now()->format('Y-m-d') . '.csv', [
                'Cabinet', 'Plan compte', 'Sièges', 'Membres', 'Propriétaire', 'Email',
                'Plan facturé', 'Période', 'Statut', 'MRR (€)', 'Fin essai', 'Créé le',
            ], function () use ($q, $plans, $priceMap) {
                foreach ($q->lazy(200) as $account) {
                    $row = $this->presentAccount($account, $plans, $priceMap);
                    yield [
                        $row['company_name'],
                        $row['account_plan'],
                        $row['seats_limit'],
                        $row['members_count'],
                        $row['owner_name'],
                        $row['owner_email'],
                        $row['plan_name'],
                        $row['period'],
                        $row['status'],
                        number_format($row['mrr'] / 100, 2, ',', ''),
                        $row['trial_ends_at'],
                        $row['created_at'],
                    ];
                }
            });
        }

        $cabinetUserIds = $this->cabinetUserIds();
        $q = User::query()
            ->with(['subscriptions' => fn ($s) => $s->where('type', 'default')->latest()])
            ->whereHas('subscriptions', fn ($s) => $s->where('type', 'default'))
            ->when($cabinetUserIds, fn ($x) => $x->whereNotIn('id', $cabinetUserIds))
            ->orderByDesc('created_at');

        return $this->streamCsv('ventes-particuliers-' . now()->format('Y-m-d') . '.csv', [
            'Nom', 'Email', 'Plan', 'Période', 'Statut', 'MRR (€)', 'Fin essai', 'Fin abonnement', 'Inscrit le',
        ], function () use ($q, $plans, $priceMap) {
            foreach ($q->lazy(200) as $user) {
                $row = $this->presentUser($user, $plans, $priceMap);
                yield [
                    $row['name'],
                    $row['email'],
                    $row['plan_name'],
                    $row['period'],
                    $row['status'],
                    number_format($row['mrr'] / 100, 2, ',', ''),
                    $row['trial_ends'],
                    $row['ends_at'],
                    $row['created_at'],
                ];
            }
        });
    }

    // -----------------------------------------------------------------------
    // Helpers privés
    // -----------------------------------------------------------------------

    private function baseQuery()
    {
        return Subscription::where('type', 'default');
    }

    /**
     * Closure de filtrage d'une relation `subscriptions` selon plan et statut,
     * réutilisable en whereHas (particuliers directs, ou via owner des cabinets).
     */
    private function subscriptionFilter(array $plans, ?string $filterPlan, ?string $filterStatus): \Closure
    {
        return function ($q) use ($plans, $filterPlan, $filterStatus) {
            $q->where('type', 'default');

            if ($filterPlan && isset($plans[$filterPlan])) {
                $priceIds = array_filter([
                    $plans[$filterPlan]['stripe_monthly'] ?? null,
                    $plans[$filterPlan]['stripe_yearly'] ?? null,
                ]);
                if ($priceIds) {
                    $q->whereIn('stripe_price', $priceIds);
                }
            }

            if ($filterStatus === 'active') {
                $q->whereNull('ends_at')
                  ->where(fn ($x) => $x->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<', now()));
            } elseif ($filterStatus === 'trialing') {
                $q->where('trial_ends_at', '>', now());
            } elseif ($filterStatus === 'cancelled') {
                $q->whereNotNull('ends_at');
            }
        };
    }

    /**
     * MRR en centimes — abonnements payants uniquement (hors essai,
     * hors résiliation en cours), contrainte optionnelle.
     */
    private function computeMrr(array $priceMap, ?\Closure $constraint = null): int
    {
        $q = $this->baseQuery()->active()->notOnGracePeriod()->notOnTrial();
        if ($constraint) {
            $constraint($q);
        }

        return (int) $q->selectRaw('stripe_price, COUNT(*) as c')
            ->groupBy('stripe_price')
            ->pluck('c', 'stripe_price')
            ->reduce(fn ($sum, $count, $price) => $sum + ($priceMap[$price] ?? 0) * $count, 0);
    }

    /**
     * IDs des utilisateurs rattachés à un cabinet (membres du pivot + propriétaires).
     * Tout abonné hors de cette liste est un « particulier ».
     *
     * @return array<int, int>
     */
    private function cabinetUserIds(): array
    {
        $memberIds = DB::table('professional_account_users as pau')
            ->join('professional_accounts as pa', 'pa.id', '=', 'pau.professional_account_id')
            ->whereNull('pa.deleted_at')
            ->pluck('pau.user_id');

        $ownerIds = DB::table('professional_accounts')
            ->whereNull('deleted_at')
            ->pluck('owner_user_id');

        return $memberIds->merge($ownerIds)->unique()->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * Tendance sur 12 mois glissants : nouveaux abonnements (segmentés),
     * MRR ajouté et résiliations effectives.
     */
    private function buildTrend(array $priceMap, array $cabinetUserIds): array
    {
        $since = now()->startOfMonth()->subMonths(11);

        $created = $this->baseQuery()
            ->where('created_at', '>=', $since)
            ->get(['user_id', 'stripe_price', 'created_at'])
            ->groupBy(fn ($s) => $s->created_at->format('Y-m'));

        $ended = $this->baseQuery()
            ->whereNotNull('ends_at')
            ->where('ends_at', '>=', $since)
            ->where('ends_at', '<=', now())
            ->get(['ends_at'])
            ->groupBy(fn ($s) => $s->ends_at->format('Y-m'));

        return collect(range(11, 0))->map(function ($i) use ($created, $ended, $priceMap, $cabinetUserIds) {
            $month = now()->startOfMonth()->subMonths($i);
            $ym    = $month->format('Y-m');
            $subs  = $created->get($ym, collect());

            return [
                'month'        => $ym,
                'label'        => ucfirst($month->translatedFormat('M y')),
                'particuliers' => $subs->reject(fn ($s) => in_array($s->user_id, $cabinetUserIds, true))->count(),
                'cabinets'     => $subs->filter(fn ($s) => in_array($s->user_id, $cabinetUserIds, true))->count(),
                'new_mrr'      => (int) $subs->sum(fn ($s) => $priceMap[$s->stripe_price] ?? 0),
                'cancelled'    => $ended->get($ym, collect())->count(),
            ];
        })->values()->all();
    }

    /** Retourne un map stripe_price_id → montant mensuel équivalent en centimes */
    private function buildPriceMap(array $plans): array
    {
        $map = [];
        foreach ($plans as $plan) {
            if (! empty($plan['stripe_monthly'])) {
                $map[$plan['stripe_monthly']] = $plan['price_monthly'];
            }
            if (! empty($plan['stripe_yearly'])) {
                $map[$plan['stripe_yearly']] = (int) round($plan['price_yearly'] / 12);
            }
        }
        return $map;
    }

    /** Résout [clé, nom, période] d'un plan depuis un price ID Stripe */
    private function planFromPrice(?string $stripePrice, array $plans): array
    {
        if ($stripePrice) {
            foreach ($plans as $key => $plan) {
                if ($stripePrice === ($plan['stripe_monthly'] ?? '')) {
                    return [$key, $plan['name'], 'monthly'];
                }
                if ($stripePrice === ($plan['stripe_yearly'] ?? '')) {
                    return [$key, $plan['name'], 'yearly'];
                }
            }
        }
        return [null, null, null];
    }

    /** Détermine le statut lisible d'un abonnement */
    private function resolveStatus(?Subscription $sub): string
    {
        if (! $sub) return 'none';
        if ($sub->onTrial()) return 'trialing';
        if ($sub->canceled() && $sub->onGracePeriod()) return 'grace';
        if ($sub->canceled()) return 'cancelled';
        if ($sub->active()) return 'active';
        return 'inactive';
    }

    /** Ligne présentable d'un abonné particulier */
    private function presentUser(User $user, array $plans, array $priceMap): array
    {
        $sub = $user->subscriptions->first();
        [$planKey, $planName, $period] = $this->planFromPrice($sub?->stripe_price, $plans);

        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'plan_key'   => $planKey,
            'plan_name'  => $planName,
            'period'     => $period,
            'status'     => $this->resolveStatus($sub),
            'mrr'        => $sub ? ($priceMap[$sub->stripe_price] ?? 0) : 0,
            'trial_ends' => $sub?->trial_ends_at?->toDateString(),
            'ends_at'    => $sub?->ends_at?->toDateString(),
            'created_at' => $user->created_at?->toDateString(),
        ];
    }

    /** Ligne présentable d'un cabinet (facturation portée par le propriétaire) */
    private function presentAccount(ProfessionalAccount $account, array $plans, array $priceMap): array
    {
        $sub = $account->owner?->subscriptions->first();
        [$planKey, $planName, $period] = $this->planFromPrice($sub?->stripe_price, $plans);

        return [
            'id'            => $account->id,
            'company_name'  => $account->company_name,
            'account_plan'  => $account->plan,
            'seats_limit'   => $account->seats_limit,
            'members_count' => $account->members_count ?? $account->members()->count(),
            'owner_name'    => $account->owner?->name,
            'owner_email'   => $account->owner?->email,
            'plan_key'      => $planKey,
            'plan_name'     => $planName,
            'period'        => $period,
            'status'        => $this->resolveStatus($sub),
            'mrr'           => $sub ? ($priceMap[$sub->stripe_price] ?? 0) : 0,
            'trial_ends_at' => $account->trial_ends_at?->toDateString(),
            'created_at'    => $account->created_at?->toDateString(),
        ];
    }
}
