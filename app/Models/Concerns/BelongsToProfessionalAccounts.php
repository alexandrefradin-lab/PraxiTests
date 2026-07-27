<?php

namespace App\Models\Concerns;

use App\Models\ProfessionalAccount;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Cloisonnement multi-tenant : rattachement aux comptes professionnels.
 *
 * Extrait de User (refactor god-model, phase 1). professionalAccountIds() est
 * la source unique utilisée par les Policies et les scopes de liste admin —
 * ne pas dupliquer cette requête ailleurs.
 */
trait BelongsToProfessionalAccounts
{
    public function professionalAccounts(): BelongsToMany
    {
        return $this->belongsToMany(ProfessionalAccount::class, 'professional_account_users')
            ->withPivot('role')->withTimestamps();
    }

    /**
     * IDs des comptes professionnels de l'utilisateur (cloisonnement multi-tenant).
     * Source unique utilisée par les Policies et les scopes de liste admin.
     *
     * @return array<int, int>
     */
    public function professionalAccountIds(): array
    {
        return $this->professionalAccounts()
            ->pluck('professional_accounts.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
