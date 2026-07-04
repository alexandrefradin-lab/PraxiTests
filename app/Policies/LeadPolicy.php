<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenant;

/**
 * A9 — cloisonnement multi-tenant des leads, centralisé ici
 * (remplace la logique manuelle authorizeLead() du contrôleur).
 */
class LeadPolicy
{
    use AuthorizesTenant;

    public function view(User $user, Lead $lead): bool
    {
        return $this->ownsAccount($user, $lead->professional_account_id);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->ownsAccount($user, $lead->professional_account_id);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->ownsAccount($user, $lead->professional_account_id);
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $this->ownsAccount($user, $lead->professional_account_id);
    }
}
