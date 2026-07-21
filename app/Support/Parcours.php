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
            ? "Ce module est encore verrouillé. Il s'ouvre pour {$seuil} points."
            : "Ce trésor est encore scellé. Il s'ouvre pour {$seuil} Éclats.";
    }

    /** Solde insuffisant pour ouvrir la mini-app choisie. */
    public static function notEnoughEclatsMessage(int $missing, ?User $user = null): string
    {
        return self::isCorporate($user)
            ? "Il vous manque {$missing} points pour débloquer ce module."
            : "Il te manque {$missing} Éclats pour ouvrir ce trésor.";
    }

    /** Confirmation d'ouverture d'une mini-app. */
    public static function unlockedMessage(string $name, int $balance, ?User $user = null): string
    {
        return self::isCorporate($user)
            ? "« {$name} » est débloqué. Solde restant : {$balance} points."
            : "« {$name} » t'appartient désormais. Il te reste {$balance} Éclats.";
    }
}
