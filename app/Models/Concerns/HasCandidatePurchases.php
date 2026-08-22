<?php

namespace App\Models\Concerns;

use App\Models\CandidatePurchase;
use App\Models\TestInvitation;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Offre particuliers (B2C) : achats one-shot et statut « parrainé ».
 *
 * Un candidat est PARRAINÉ s'il est entré par une invitation professionnelle :
 * son accès est porté par l'abonnement du professionnel, jamais par le paywall
 * particulier. Le lien se lit de deux façons (défense en profondeur) : une
 * invitation émise à son adresse email, ou une tentative déjà rattachée à une
 * invitation (cas d'un email de compte différent de l'email d'invitation).
 */
trait HasCandidatePurchases
{
    public function candidatePurchases(): HasMany
    {
        return $this->hasMany(CandidatePurchase::class);
    }

    /** A payé le déblocage particulier (n'importe quel produit b2c). */
    public function hasPaidB2cUnlock(): bool
    {
        return $this->candidatePurchases()->paid()->exists();
    }

    /** Est entré par une invitation professionnelle (accès porté par le pro). */
    public function isSponsoredCandidate(): bool
    {
        return TestInvitation::where('email', $this->email)->exists()
            || $this->attempts()->whereNotNull('invitation_id')->exists();
    }
}
