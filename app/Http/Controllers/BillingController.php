<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Exceptions\IncompletePayment;

class BillingController extends Controller
{
    /**
     * Page de sélection des plans.
     */
    public function plans(Request $request): Response
    {
        $user  = $request->user();
        $plans = config('plans.plans');

        // Détecter le plan actif et la période (monthly/yearly)
        $activePlan   = null;
        $activePeriod = null;

        if ($user->subscribed('default')) {
            $sub = $user->subscription('default');

            foreach ($plans as $key => $plan) {
                if ($sub->hasStripePrice($plan['stripe_monthly'])) {
                    $activePlan   = $key;
                    $activePeriod = 'monthly';
                    break;
                }
                if ($sub->hasStripePrice($plan['stripe_yearly'])) {
                    $activePlan   = $key;
                    $activePeriod = 'yearly';
                    break;
                }
            }
        }

        return Inertia::render('Billing/Plans', [
            'plans'        => $plans,
            'trialDays'    => config('plans.default_trial_days'),
            'activePlan'   => $activePlan,
            'activePeriod' => $activePeriod,
            'onTrial'      => $user->onTrial('default'),
            'subscribed'   => $user->subscribed('default'),
        ]);
    }

    /**
     * Crée une session Stripe Checkout pour souscrire à un plan.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan'   => ['required', 'string', 'in:' . implode(',', array_keys(config('plans.plans')))],
            'period' => ['required', 'string', 'in:monthly,yearly'],
        ]);

        $user      = $request->user();
        $plan      = config('plans.plans.' . $request->plan);
        $priceId   = $request->period === 'yearly'
            ? $plan['stripe_yearly']
            : $plan['stripe_monthly'];
        $trialDays = config('plans.default_trial_days');

        // Si déjà abonné → swap de plan plutôt que nouveau checkout
        if ($user->subscribed('default')) {
            try {
                $user->subscription('default')->swap($priceId);
            } catch (IncompletePayment $e) {
                return redirect()->route('cashier.payment', [$e->payment->id, 'redirect' => route('billing.manage')]);
            } catch (\Throwable $e) {
                Log::error('Stripe swap failed', ['user_id' => $user->id, 'status' => method_exists($e, 'getCode') ? $e->getCode() : null]);
                return redirect()->route('billing.manage')
                    ->with('error', "La mise à jour de l'abonnement a échoué. Réessaie ou contacte le support.");
            }

            return redirect()->route('billing.manage')
                ->with('success', 'Ton abonnement a été mis à jour.');
        }

        try {
            $checkout = $user
                ->newSubscription('default', $priceId)
                ->trialDays($trialDays)
                ->allowPromotionCodes()
                ->checkout([
                    'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'  => route('billing.plans'),
                ]);

            return Inertia::location($checkout->url);
        } catch (IncompletePayment $e) {
            return redirect()->route('cashier.payment', [$e->payment->id, 'redirect' => route('billing.manage')]);
        }
    }

    /**
     * Page de gestion de l'abonnement (portail Stripe).
     */
    public function manage(Request $request): Response
    {
        $user = $request->user();

        $subscription  = $user->subscription('default');
        $invoices      = $user->invoices();
        $paymentMethod = $user->defaultPaymentMethod();

        // Détails du plan actif
        $activePlanKey  = null;
        $activePlanName = null;
        $activePeriod   = null;

        if ($subscription) {
            foreach (config('plans.plans') as $key => $plan) {
                if ($subscription->hasStripePrice($plan['stripe_monthly'])) {
                    $activePlanKey  = $key;
                    $activePlanName = $plan['name'];
                    $activePeriod   = 'monthly';
                    break;
                }
                if ($subscription->hasStripePrice($plan['stripe_yearly'])) {
                    $activePlanKey  = $key;
                    $activePlanName = $plan['name'];
                    $activePeriod   = 'yearly';
                    break;
                }
            }
        }

        return Inertia::render('Billing/Manage', [
            'subscribed'     => $user->subscribed('default'),
            'onTrial'        => $user->onTrial('default'),
            'trialEndsAt'    => $user->trialEndsAt('default')?->toDateString(),
            'endsAt'         => $subscription?->ends_at?->toDateString(),
            'onGracePeriod'  => $subscription?->onGracePeriod() ?? false,
            'activePlanKey'  => $activePlanKey,
            'activePlanName' => $activePlanName,
            'activePeriod'   => $activePeriod,
            'card'           => $paymentMethod ? [
                'brand'    => $paymentMethod->card->brand,
                'last4'    => $paymentMethod->card->last4,
                'exp'      => $paymentMethod->card->exp_month . '/' . $paymentMethod->card->exp_year,
            ] : null,
            'invoices' => $invoices->map(fn ($inv) => [
                'id'     => $inv->id,
                'date'   => $inv->date()->toDateString(),
                'total'  => $inv->total(),
                'status' => $inv->status,
                'url'    => $inv->hosted_invoice_url,
            ])->values()->toArray(),
        ]);
    }

    /**
     * Redirige vers le portail client Stripe (gestion CB, annulation…).
     */
    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('billing.manage'));
    }

    /**
     * Page de succès après Checkout.
     */
    public function success(Request $request): Response
    {
        return Inertia::render('Billing/Success');
    }

    /**
     * Annulation de l'abonnement (fin de période).
     */
    public function cancel(Request $request)
    {
        abort_unless($request->user()->subscribed('default'), 422, 'Aucun abonnement actif.');

        try {
            $request->user()->subscription('default')->cancel();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe error', ['msg' => $e->getMessage(), 'user' => auth()->id()]);
            return back()->withErrors(['stripe' => 'Une erreur Stripe est survenue. Réessayez dans quelques instants.']);
        } catch (\Exception $e) {
            Log::error('Billing error', ['msg' => $e->getMessage()]);
            return back()->withErrors(['stripe' => 'Une erreur inattendue est survenue.']);
        }

        return redirect()->route('billing.manage')
            ->with('success', 'Ton abonnement sera actif jusqu\'à la fin de la période en cours.');
    }

    /**
     * Réactivation d'un abonnement en grace period.
     */
    public function resume(Request $request)
    {
        abort_unless($request->user()->subscribed('default'), 422, 'Aucun abonnement actif.');

        try {
            $request->user()->subscription('default')->resume();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe error', ['msg' => $e->getMessage(), 'user' => auth()->id()]);
            return back()->withErrors(['stripe' => 'Une erreur Stripe est survenue. Réessayez dans quelques instants.']);
        } catch (\Exception $e) {
            Log::error('Billing error', ['msg' => $e->getMessage()]);
            return back()->withErrors(['stripe' => 'Une erreur inattendue est survenue.']);
        }

        return redirect()->route('billing.manage')
            ->with('success', 'Ton abonnement a été réactivé.');
    }
}
