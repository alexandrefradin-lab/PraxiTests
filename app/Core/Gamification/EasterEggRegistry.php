<?php

namespace Praxis\Core\Gamification;

/**
 * Registre des secrets de PraxiQuest.
 *
 * Source de vérité CÔTÉ SERVEUR des récompenses : le client envoie un slug,
 * jamais un montant d'Éclats ni un badge. Un slug inconnu est rejeté.
 */
final class EasterEggRegistry
{
    /**
     * slug => ['eclats' => int, 'badge' => string slug de badge]
     *
     * Les badges associés doivent exister en base (cf. BadgeSeeder) et porter
     * un xp_reward à 0 : les Éclats sont crédités ici, pas par le badge.
     */
    private const EGGS = [
        // Séquence Konami dans le layout candidat.
        'konami' => ['eclats' => 42, 'badge' => 'eveille'],

        // Lien discret sur la page 404 → /nulle-part.
        'faux_bouton' => ['eclats' => 13, 'badge' => 'egare'],

        // Grimoire parcouru à rebours, au clavier uniquement.
        'grimoire_inverse' => ['eclats' => 33, 'badge' => 'scribe'],
    ];

    /** @return array{eclats:int,badge:string}|null */
    public static function get(string $slug): ?array
    {
        return self::EGGS[$slug] ?? null;
    }

    /** @return list<string> */
    public static function slugs(): array
    {
        return array_keys(self::EGGS);
    }
}
