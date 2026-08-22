<?php

namespace App\Listeners;

use App\Models\CandidatePurchase;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * Filet de sécurité du paiement one-shot particulier (offre B2C).
 *
 * UnlockController::success() marque l'achat payé au retour de checkout, mais
 * si l'utilisateur ferme l'onglet avant la redirection, seul le webhook Stripe
 * checkout.session.completed porte l'information. Ce listener retrouve l'achat
 * par son id (metadata.b2c_purchase_id) ou, à défaut, par l'id de session.
 *
 * Prérequis : ajouter checkout.session.completed aux événements de l'endpoint
 * webhook dans le dashboard Stripe (cf. .env.example).
 */
class RecordB2cPurchaseOnCheckoutCompleted
{
    public function handle(WebhookReceived $event): void
    {
        if (($event->payload['type'] ?? null) !== 'checkout.session.completed') {
            return;
        }

        $session = $event->payload['data']['object'] ?? [];

        // Ne concerne que nos checkouts B2C (les abonnements pro passent par
        // customer.subscription.created) — et uniquement s'ils sont payés.
        $purchaseId = $session['metadata']['b2c_purchase_id'] ?? null;
        if (! $purchaseId || ($session['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $purchase = CandidatePurchase::find($purchaseId)
            ?? CandidatePurchase::where('stripe_session_id', $session['id'] ?? '')->first();

        if (! $purchase) {
            Log::warning('Webhook B2C : achat introuvable', ['purchase_id' => $purchaseId, 'session' => $session['id'] ?? null]);
            return;
        }

        $purchase->markPaid();
        Log::info('Achat B2C confirmé par webhook', ['purchase_id' => $purchase->id, 'user_id' => $purchase->user_id]);
    }
}
