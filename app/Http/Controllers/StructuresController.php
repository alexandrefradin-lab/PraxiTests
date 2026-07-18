<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page de vente publique « Structures » (/structures).
 *
 * Contenu calqué sur public/docs/presentation-structures.pdf : pitch,
 * chiffres, parcours bénéficiaire, conformité, grille tarifaire, programme
 * pilote. Le paiement démarre depuis cette page : le CTA passe par start()
 * qui mémorise l'intention d'achat en session (subscribe_intent), puis le
 * flux inscription → vérification email → checkout Stripe la consomme
 * (cf. AuthController::register, EmailVerificationController::verify,
 * BillingController::subscribe).
 */
class StructuresController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Public/Structures', [
            'plans'     => config('plans.plans'),
            'trialDays' => config('plans.default_trial_days'),
            'contact'   => config('praxiquest.contact.email', 'contact@praxiquest.fr'),
        ]);
    }

    /**
     * Point d'entrée du CTA paiement (accessible sans compte).
     * Mémorise le plan choisi puis route selon l'état de l'utilisateur.
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'plan'   => ['required', 'string', 'in:' . implode(',', array_keys(config('plans.plans')))],
            'period' => ['required', 'string', 'in:monthly,yearly'],
        ]);

        $plan = config('plans.plans.' . $data['plan']);

        if (! ($plan['available'] ?? true)) {
            return redirect()->route('structures.show')
                ->with('error', "Le palier {$plan['name']} arrive bientôt. Contactez-nous : " . config('praxiquest.contact.email'));
        }

        // Intention d'achat : survit à l'inscription et à la vérification email.
        $request->session()->put('subscribe_intent', $data);

        if ($request->user()) {
            return redirect()->route('billing.subscribe');
        }

        return redirect()->route('register');
    }
}
