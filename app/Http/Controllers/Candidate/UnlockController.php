<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\CandidatePurchase;
use App\Support\B2c;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Cashier\Cashier;

/**
 * Déblocage du parcours particulier (paiement one-shot — config/b2c.php).
 *
 * show()     : page de conversion (« paywall ») avec les offres.
 * checkout() : crée l'achat en pending (preuve CGV + renonciation rétractation)
 *              puis redirige vers Stripe Checkout en mode paiement unique.
 * success()  : retour de Stripe — vérifie la session côté API et marque payé.
 *              Filet de sécurité symétrique : le webhook
 *              checkout.session.completed (RecordB2cPurchaseOnCheckoutCompleted)
 *              fait la même chose si l'utilisateur ferme l'onglet avant le retour.
 */
class UnlockController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Rien à vendre à qui a déjà tout : pros/admins, parrainés, déjà payé.
        if (! B2c::enforced() || B2c::hasFullAccess($user)) {
            return redirect()->route('tests.index');
        }

        return Inertia::render('Candidate/Unlock', [
            'products'      => B2c::products(),
            'freeTestSlugs' => (array) config('b2c.free_test_slugs', []),
        ]);
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'product' => ['required', 'string', 'in:' . implode(',', array_keys(config('b2c.products', [])))],
            'cgv'     => ['required', 'accepted'],
        ], [
            'cgv.required' => 'Tu dois accepter les Conditions Générales de Vente pour continuer.',
            'cgv.accepted' => 'Tu dois accepter les Conditions Générales de Vente pour continuer.',
        ]);

        $user    = $request->user();
        $product = config('b2c.products.' . $data['product']);

        if (! ($product['available'] ?? true)) {
            return redirect()->route('b2c.unlock')
                ->with('error', "L'offre {$product['name']} arrive bientôt. Contacte-nous pour être prévenu·e : " . config('praxiquest.contact.email'));
        }

        // Garde-fou : Price ID Stripe non renseigné (cf. BillingController::checkout).
        if (blank($product['stripe_price'])) {
            Log::warning('Checkout B2C sans Price ID Stripe', ['product' => $data['product']]);
            return redirect()->route('b2c.unlock')
                ->with('error', "Cette offre n'est pas encore disponible. Réessaie plus tard ou contacte le support.");
        }

        if ($user->hasPaidB2cUnlock()) {
            return redirect()->route('tests.index')
                ->with('success', 'Ton parcours est déjà débloqué.');
        }

        // Trace d'achat + preuve d'acceptation des CGV et de la demande
        // d'exécution immédiate (renonciation au droit de rétractation pour un
        // contenu numérique fourni immédiatement — art. L221-28 1° C. conso).
        $purchase = CandidatePurchase::create([
            'user_id'              => $user->id,
            'product'              => $data['product'],
            'amount'               => $product['price'],
            'currency'             => 'eur',
            'status'               => 'pending',
            'cgv_accepted_at'      => now(),
            'withdrawal_waived_at' => now(),
        ]);

        try {
            $checkout = $user->checkout([$product['stripe_price'] => 1], [
                'success_url' => route('b2c.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('b2c.unlock'),
                'metadata'    => [
                    'b2c_purchase_id' => (string) $purchase->id,
                    'b2c_product'     => $data['product'],
                ],
            ]);

            $purchase->update(['stripe_session_id' => $checkout->id]);

            return Inertia::location($checkout->url);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout B2C failed', ['user_id' => $user->id, 'product' => $data['product'], 'msg' => $e->getMessage()]);
            return redirect()->route('b2c.unlock')
                ->with('error', "Le paiement n'a pas pu être initié. Réessaie dans un instant ou contacte le support.");
        }
    }

    public function success(Request $request)
    {
        $user      = $request->user();
        $sessionId = (string) $request->query('session_id', '');

        // Retrouver l'achat par la session Stripe — et vérifier qu'il appartient
        // bien à l'utilisateur connecté (le session_id est un secret d'URL, mais
        // on ne marque jamais payé l'achat d'un autre compte).
        $purchase = $sessionId !== ''
            ? CandidatePurchase::where('stripe_session_id', $sessionId)
                ->where('user_id', $user->id)
                ->first()
            : null;

        if ($purchase && $purchase->status !== 'paid') {
            try {
                $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
                if (($session->payment_status ?? null) === 'paid') {
                    $purchase->markPaid();
                }
            } catch (\Throwable $e) {
                // Le webhook checkout.session.completed prendra le relais.
                Log::warning('Vérification session Stripe B2C impossible au retour', ['session' => $sessionId, 'msg' => $e->getMessage()]);
            }
        }

        return Inertia::render('Candidate/UnlockSuccess', [
            'paid' => (bool) $user->hasPaidB2cUnlock(),
        ]);
    }
}
