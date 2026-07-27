<?php

namespace App\Models\Concerns;

use App\Models\ProfileGrimoire;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Grimoire global du candidat (synthèse IA cross-tests).
 *
 * Extrait de User (refactor god-model, phase 1). La génération du contenu vit
 * dans GlobalGrimoireService ; ici uniquement l'accès et la création paresseuse.
 */
trait HasGrimoire
{
    public function profileGrimoire(): HasOne
    {
        return $this->hasOne(ProfileGrimoire::class);
    }

    /** Récupère le Grimoire global du candidat, en le créant si absent. */
    public function getOrCreateGrimoire(): ProfileGrimoire
    {
        return $this->profileGrimoire()->firstOrCreate(['user_id' => $this->id]);
    }
}
