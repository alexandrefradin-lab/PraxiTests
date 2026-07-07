<?php

namespace App\Support;

use App\Models\User;

/**
 * Registre du parcours visuel côté serveur (messages flash, gating…).
 * Pendant PHP du composable front useParcours : 'medieval' (défaut) tutoie
 * et parle d'Éclats/trésors ; 'corporate' vouvoie et parle de points/modules.
 */
class Parcours
{
    public static function isCorporate(?User $user = null): bool
    {
        $user ??= auth()->user();

        return ($user?->ui_theme ?? 'medieval') === 'corporate';
    }

    /** Nom de l'unité de gamification selon le parcours. */
    public static function xpName(?User $user = null): string
    {
        return self::isCorporate($user) ? 'points' : 'Éclats';
    }

    /** Message de gating d'un module/trésor encore verrouillé. */
    public static function sealedMessage(int $seuil, ?User $user = null): string
    {
        return self::isCorporate($user)
            ? "Ce module est encore verrouillé. Il se débloque à {$seuil} points."
            : "Ce trésor est encore scellé. Il se révèle à {$seuil} Éclats.";
    }
}
