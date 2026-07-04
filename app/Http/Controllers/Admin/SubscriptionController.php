<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StreamsCsv;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Cashier\Subscription;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionController extends Controller
{
    use StreamsCsv;

    public function index(Request $request)
    {
        $plans      = config('plans.plans');
        $filterPlan = $request->get('plan');
        $filterStatus = $request->get('status'); // active | trialing | cancelled | none

        // --- KPIs ---
        $allSubs = Subscription::where('name', 'default')->get();

        $activeSubs   = $allSubs->filter(fn ($s) => $s->active() && ! $s->onGracePeriod());
        $trialSubs    = $allSubs->filter(fn ($s) => $s->onTrial());
        $cancelledSubs = $allSubs->filter(fn ($s) => $s->cancelled());

        // MRR : calcul basé sur les price IDs Stripe → montant mensuel
        $priceMap = $this->buildPriceMap($plans);
        $mrr = $activeSubs->sum(function ($sub) use ($priceMap) {
            return $priceMap[$sub->stripe_price] ?? 0;
        });

        // --- Liste utilisateurs avec abonnement ---
        $query = User::with(['subscription' => fn ($q) => $q->where('name', 'default')])
            ->orderByDesc('created_at');

        // Recherche nom / email
        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(fn ($x) => $x->where('email', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
        }

        // Filtre plan
        if ($filterPlan && isset($plans[$filterPlan])) {
            $priceIds = array_filter([
                $plans[$filterPlan]['stripe_monthly'] ?? null,
                $plans[$filterPlan]['stripe_yearly']  ?? null,
            ]);
            if ($priceIds) {
                $query->whereHas('subscriptions', fn ($q) =>
                    $q->where('name', 'default')->whereIn('stripe_price', $priceIds)
                );
            }
        }

        // Filtre statut
        if ($filterStatus === 'active') {
            $query->whereHas('subscriptions', fn ($q) =>
                $q->where('name', 'default')->whereNull('ends_at')->whereNotNull('trial_ends_at')->where('trial_ends_at', '<', now())
                  ->orWhere(fn ($q2) => $q2->where('name', 'default')->whereNull('ends_at')->whereNull('trial_ends_at'))
            );
        } elseif ($filterStatus === 'trialing') {
            $query->whereHas('subscriptions', fn ($q) =>
                $q->where('name', 'default')->where('trial_ends_at', '>', now())
            );
        } elseif ($filterStatus === 'cancelled') {
            $query->whereHas('subscriptions', fn ($q) =>
                $q->where('name', 'default')->whereNotNull('ends_at')
            );
        } elseif ($filterStatus === 'none') {
            $query->whereDoesntHave('subscriptions', fn ($q) =>
                $q->where('name', 'default')
            );
        }

        $users = $query->paginate(25)->withQueryString();

        // Enrichir chaque user avec les infos plan + statut
        $users->getCollection()->transform(function ($user) use ($plans, $priceMap) {
            $sub = $user->subscription;

            $planKey  = null;
            $planName = null;
            $period   = null;

            if ($sub) {
                foreach ($plans as $key => $plan) {
                    if ($sub->stripe_price === ($plan['stripe_monthly'] ?? '')) {
                        $planKey  = $key;
                        $planName = $plan['name'];
                        $period   = 'monthly';
                        break;
                    }
                    if ($sub->stripe_price === ($plan['stripe_yearly'] ?? '')) {
                        $planKey  = $key;
                        $planName = $plan['name'];
                        $period   = 'yearly';
                        break;
                    }
                }
            }

            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at?->toDateString(),
                'plan_key'   => $planKey,
                'plan_name'  => $planName,
                'period'     => $period,
                'status'     => $this->resolveStatus($sub, $user),
                'trial_ends' => $sub?->trial_ends_at?->toDateString(),
                'ends_at'    => $sub?->ends_at?->toDateString(),
                'mrr'        => $sub ? ($priceMap[$sub->stripe_price] ?? 0) : 0,
            ];
        });

        return Inertia::render('Admin/Subscriptions/Index', [
            'users'    => $users,
            'plans'    => collect($plans)->map(fn ($p, $k) => ['key' => $k, 'name' => $p['name']])->values(),
            'filters'  => ['plan' => $filterPlan, 'status' => $filterStatus, 'search' => $request->get('search')],
            'kpis'     => [
                'total_subscribers' => $activeSubs->count() + $trialSubs->count(),
                'active'            => $activeSubs->count(),
                'trialing'          => $trialSubs->count(),
                'cancelled'         => $cancelledSubs->count(),
                'mrr'               => $mrr,   // en centimes
            ],
        ]);
    }

    /**
     * Export CSV des abonnés (tous les utilisateurs ayant un abonnement).
     * Sans pagination — parcours lazy pour rester sobre en mémoire.
     */
    public function export(): StreamedResponse
    {
        $plans    = config('plans.plans');
        $priceMap = $this->buildPriceMap($plans);

        \App\Models\AuditLog::record('subscription.exported', null, []);

        $q = User::with(['subscription' => fn ($sub) => $sub->where('name', 'default')])
            ->whereHas('subscriptions', fn ($sub) => $sub->where('name', 'default'))
            ->orderByDesc('created_at');

        return $this->streamCsv('abonnements-' . now()->format('Y-m-d') . '.csv', [
            'Nom', 'Email', 'Plan', 'Période', 'Statut', 'Fin essai', 'Fin abonnement', 'MRR (€)', 'Inscrit le',
        ], function () use ($q, $plans, $priceMap) {
            foreach ($q->lazy(200) as $user) {
                $sub = $user->subscription;
                $planName = null;
                $period   = null;
                foreach ($plans as $plan) {
                    if ($sub && $sub->stripe_price === ($plan['stripe_monthly'] ?? '')) { $planName = $plan['name']; $period = 'mensuel'; break; }
                    if ($sub && $sub->stripe_price === ($plan['stripe_yearly'] ?? ''))  { $planName = $plan['name']; $period = 'annuel'; break; }
                }
                yield [
                    $user->name,
                    $user->email,
                    $planName,
                    $period,
                    $this->resolveStatus($sub, $user),
                    $sub?->trial_ends_at?->format('d/m/Y'),
                    $sub?->ends_at?->format('d/m/Y'),
                    $sub ? number_format(($priceMap[$sub->stripe_price] ?? 0) / 100, 2, ',', '') : '0',
                    $user->created_at?->format('d/m/Y'),
                ];
            }
        });
    }

    // -----------------------------------------------------------------------
    // Helpers privés
    // -----------------------------------------------------------------------

    /** Retourne un map stripe_price_id → montant mensuel en centimes */
    private function buildPriceMap(array $plans): array
    {
        $map = [];
        foreach ($plans as $plan) {
            if (! empty($plan['stripe_monthly'])) {
                $map[$plan['stripe_monthly']] = $plan['price_monthly'];
            }
            if (! empty($plan['stripe_yearly'])) {
                // Ramener le yearly à un équivalent mensuel
                $map[$plan['stripe_yearly']] = (int) round($plan['price_yearly'] / 12);
            }
        }
        return $map;
    }

    /** Détermine le statut lisible d'un abonnement */
    private function resolveStatus(?Subscription $sub, User $user): string
    {
        if (! $sub) return 'none';
        if ($sub->onTrial()) return 'trialing';
        if ($sub->cancelled() && $sub->onGracePeriod()) return 'grace';
        if ($sub->cancelled()) return 'cancelled';
        if ($sub->active()) return 'active';
        return 'inactive';
    }
}
