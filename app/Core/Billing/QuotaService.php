<?php

namespace Praxis\Core\Billing;

use App\Models\TestInvitation;
use App\Models\User;

/**
 * Quota mensuel de dossiers candidats (grille V1).
 *
 * Un « dossier » = une invitation candidat (TestInvitation). Le quota vient du
 * plan actif de l'utilisateur (config plans.quota_dossiers) ; sans abonnement
 * identifiable (essai en cours, plan inconnu), on retombe sur le quota du plan
 * d'entrée 'independant'.
 *
 * Le quota n'est appliqué que si le paywall est actif
 * (config praxiquest.billing.enforced) — sinon comportement bêta inchangé.
 * Les admins ne sont jamais soumis au quota.
 */
class QuotaService
{
    /**
     * L'utilisateur peut-il créer un nouveau dossier ce mois-ci ?
     */
    public function canCreateDossier(User $user): bool
    {
        if (! config('praxiquest.billing.enforced')) {
            return true;
        }

        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        $quota = $this->dossierQuota($user);

        if ($quota === null) {
            return true; // plan sans limite définie
        }

        return $this->usedThisMonth($user) < $quota;
    }

    /**
     * Quota mensuel du plan actif (null = illimité).
     */
    public function dossierQuota(User $user): ?int
    {
        $plans = config('plans.plans', []);
        $key   = $this->activePlanKey($user) ?? 'independant';

        $quota = $plans[$key]['quota_dossiers'] ?? null;

        return $quota === null ? null : (int) $quota;
    }

    /**
     * Dossiers (invitations) créés par cet utilisateur sur le mois calendaire
     * en cours. withTrashed() : supprimer une invitation ne rend pas le crédit,
     * sinon le quota se contourne à la corbeille.
     */
    public function usedThisMonth(User $user): int
    {
        return TestInvitation::withTrashed()
            ->where('created_by', $user->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    /**
     * Clé du plan actif (même logique de résolution que BillingController :
     * correspondance du Price Stripe de l'abonnement 'default').
     */
    public function activePlanKey(User $user): ?string
    {
        if (! $user->subscribed('default')) {
            return null;
        }

        $subscription = $user->subscription('default');

        foreach (config('plans.plans', []) as $key => $plan) {
            if (filled($plan['stripe_monthly']) && $subscription->hasPrice($plan['stripe_monthly'])) {
                return $key;
            }
            if (filled($plan['stripe_yearly']) && $subscription->hasPrice($plan['stripe_yearly'])) {
                return $key;
            }
        }

        return null;
    }
}
