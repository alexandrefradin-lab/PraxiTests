<?php

namespace App\Support;

use App\Models\Test;
use App\Models\User;

/**
 * Source unique des règles d'accès de l'offre particuliers (config/b2c.php).
 *
 * Le paywall particulier ne concerne QUE les candidats auto-inscrits :
 * professionnels, admins et candidats parrainés (invités par un pro) passent
 * toujours. Tant que b2c.enforced est false, personne n'est verrouillé.
 */
class B2c
{
    public static function enforced(): bool
    {
        return (bool) config('b2c.enforced');
    }

    /** L'épreuve est-elle offerte aux auto-inscrits (épreuve d'appel) ? */
    public static function isFreeTest(Test $test): bool
    {
        return in_array($test->slug, (array) config('b2c.free_test_slugs', []), true);
    }

    /** Accès complet, indépendamment du paywall (rôles, parrainage, achat). */
    public static function hasFullAccess(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'professional'])
            || $user->firstProfessionalAccountId() !== null
            || $user->hasPaidB2cUnlock()
            || $user->isSponsoredCandidate();
    }

    /** Vrai si le paywall particulier doit bloquer cet utilisateur. */
    public static function locked(User $user): bool
    {
        return self::enforced() && ! self::hasFullAccess($user);
    }

    /**
     * Produits achetables, prêts pour l'affichage (page de déblocage).
     * Les produits non disponibles restent listés (teasing « bientôt ») mais
     * ne sont pas souscriptibles — même convention que config/plans.php.
     */
    public static function products(): array
    {
        return collect(config('b2c.products', []))
            ->map(fn (array $p, string $key) => [
                'key'         => $key,
                'name'        => $p['name'],
                'description' => $p['description'],
                'price'       => $p['price'],
                'available'   => (bool) ($p['available'] ?? true),
                'features'    => $p['features'] ?? [],
                'highlighted' => (bool) ($p['highlighted'] ?? false),
            ])
            ->values()
            ->all();
    }
}
