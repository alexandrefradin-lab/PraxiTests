<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Règle multi-tenant partagée par les Policies admin :
 * un admin accède à tout ; un professionnel uniquement aux ressources
 * rattachées à l'un de ses comptes professionnels.
 */
trait AuthorizesTenant
{
    protected function ownsAccount(User $user, ?int $professionalAccountId): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $professionalAccountId !== null
            && in_array($professionalAccountId, $user->professionalAccountIds(), true);
    }
}
