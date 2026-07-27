<?php

namespace App\Models\Concerns;

/**
 * 2FA TOTP : détection d'activation et consommation des codes de récupération.
 *
 * Extrait de User (refactor god-model, phase 1). Les casts associés
 * (two_factor_secret => encrypted, two_factor_recovery_codes => array) restent
 * déclarés sur User, avec les exclusions $fillable/$hidden (SEC-C1/C2).
 */
trait HasTwoFactorAuthentication
{
    /** Indique si le 2FA est activé (secret présent). */
    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret);
    }

    /**
     * Vérifie si un code de récupération est valide, et le consomme si oui.
     *
     * SEC-M3: Les codes sont stockés sous forme de hachés SHA-256 en base.
     * Le code en clair soumis par l'utilisateur est haché à la volée pour
     * la comparaison via hash_equals() (protection contre les timing attacks).
     * Les codes en clair ne sont affichés qu'une seule fois lors de la
     * génération (TwoFactorController::enable / regenerateCodes).
     */
    public function useRecoveryCode(string $code): bool
    {
        $code     = strtoupper(trim($code));
        $codeHash = hash('sha256', $code);
        $codes    = $this->two_factor_recovery_codes ?? [];

        // Parcours complet avec hash_equals() pour résistance aux timing attacks.
        $found     = false;
        $remaining = [];
        foreach ($codes as $storedHash) {
            if (!$found && hash_equals($storedHash, $codeHash)) {
                $found = true; // code consommé — ne pas le conserver
            } else {
                $remaining[] = $storedHash;
            }
        }

        if (!$found) return false;

        // Consommer le code (usage unique).
        // forceFill : two_factor_recovery_codes est hors $fillable (anti
        // mass-assignment) ; update()/updateQuietly() passerait par fill() qui
        // l'ignore silencieusement → la consommation ne serait jamais persistée
        // et le code resterait réutilisable (bug de sécurité, audit Phase 0).
        $this->forceFill(['two_factor_recovery_codes' => $remaining])->saveQuietly();

        return true;
    }
}
