<?php

namespace App\Policies;

use App\Models\TestInvitation;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenant;

/**
 * Cloisonnement multi-tenant des invitations candidat.
 * NB : professional_account_id est null pour les invitations créées par un
 * admin sans compte PA — elles ne sont donc visibles que des admins.
 */
class TestInvitationPolicy
{
    use AuthorizesTenant;

    public function view(User $user, TestInvitation $invitation): bool
    {
        return $this->ownsAccount($user, $invitation->professional_account_id);
    }

    public function resend(User $user, TestInvitation $invitation): bool
    {
        return $this->ownsAccount($user, $invitation->professional_account_id);
    }

    public function delete(User $user, TestInvitation $invitation): bool
    {
        return $this->ownsAccount($user, $invitation->professional_account_id);
    }

    public function restore(User $user, TestInvitation $invitation): bool
    {
        return $this->ownsAccount($user, $invitation->professional_account_id);
    }
}
