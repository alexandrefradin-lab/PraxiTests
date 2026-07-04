<?php

namespace App\Policies;

use App\Models\EmailCampaign;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenant;

/**
 * A10 — cloisonnement multi-tenant des campagnes email
 * (remplace findAndAuthorizeCampaign() du contrôleur).
 */
class EmailCampaignPolicy
{
    use AuthorizesTenant;

    public function view(User $user, EmailCampaign $campaign): bool
    {
        return $this->ownsAccount($user, $campaign->professional_account_id);
    }

    public function update(User $user, EmailCampaign $campaign): bool
    {
        return $this->ownsAccount($user, $campaign->professional_account_id);
    }

    public function delete(User $user, EmailCampaign $campaign): bool
    {
        return $this->ownsAccount($user, $campaign->professional_account_id);
    }

    public function restore(User $user, EmailCampaign $campaign): bool
    {
        return $this->ownsAccount($user, $campaign->professional_account_id);
    }

    public function send(User $user, EmailCampaign $campaign): bool
    {
        return $this->ownsAccount($user, $campaign->professional_account_id);
    }
}
