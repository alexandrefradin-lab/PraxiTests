<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * Un abonnement Stripe = un compte professionnel.
 *
 * L'inscription libre crée des comptes « candidat » ; le rôle 'professional'
 * (accès espace conseiller : invitations, suivi des dossiers) n'était attribué
 * que manuellement par un admin. Depuis la PDV /structures, un consultant qui
 * souscrit doit obtenir cet accès automatiquement : ce listener l'attribue à
 * la réception du webhook customer.subscription.created.
 *
 * Filet de sécurité complémentaire dans BillingController::success() (si le
 * webhook arrive en retard, le rôle est posé au retour du checkout).
 */
class GrantProfessionalRoleOnSubscription
{
    public function handle(WebhookReceived $event): void
    {
        if (($event->payload['type'] ?? null) !== 'customer.subscription.created') {
            return;
        }

        $stripeCustomerId = $event->payload['data']['object']['customer'] ?? null;
        if (! $stripeCustomerId) {
            return;
        }

        $user = User::where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        if (! $user->hasRole('professional') && ! $user->hasRole('admin')) {
            $user->assignRole('professional');
            Log::info('Rôle professional attribué à l\'abonnement', ['user_id' => $user->id]);
        }
    }
}
