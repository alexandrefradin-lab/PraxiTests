<?php

namespace Praxis\Plugins\PraxiMum\Archetypes;

/**
 * Système d'archétypes Big Five : 16 profils mémorables.
 *
 * Logique : on convertit chaque dimension OCEAN en H (T ≥ 50) ou L (T < 50),
 * ce qui donne une clé à 5 lettres (ex : 'HHHHL').
 * Si la clé existe dans le map, on retourne directement l'archétype.
 * Sinon, on prend l'archétype le plus proche (distance de Hamming minimale).
 */
class ArchetypeResolver
{
    /** Cache du JSON map. */
    protected static ?array $map = null;

    public static function map(): array
    {
        if (self::$map === null) {
            $path = __DIR__ . '/../Data/archetypes.json';
            if (!file_exists($path)) {
                throw new \RuntimeException("ArchetypeResolver: fichier introuvable : {$path}");
            }
            self::$map = json_decode(
                file_get_contents($path),
                true
            ) ?: [];
        }
        return self::$map;
    }

    /**
     * Résout l'archétype correspondant aux scores T des 5 dimensions.
     *
     * @param array $dimScores  format ['O' => ['T' => int, ...], 'C' => [...], ...]
     * @return array|null
     */
    public static function resolve(array $dimScores): ?array
    {
        $key = '';
        foreach (['O', 'C', 'E', 'A', 'N'] as $dim) {
            $T = $dimScores[$dim]['T'] ?? 50;
            $key .= $T >= 50 ? 'H' : 'L';
        }

        $map = self::map();
        if (isset($map[$key])) {
            $arch = $map[$key];
            $arch['matched_key'] = $key;
            $arch['distance']    = 0;
            return $arch;
        }

        // Fallback : Hamming minimal.
        $best = null;
        $bestDist = PHP_INT_MAX;
        foreach ($map as $k => $candidate) {
            $dist = 0;
            for ($i = 0; $i < 5; $i++) {
                if ($k[$i] !== $key[$i]) $dist++;
            }
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = $candidate;
                $best['matched_key'] = $k;
                $best['distance']    = $dist;
            }
        }
        return $best;
    }

    /** Retourne tous les archétypes (utile pour admin / catalogue). */
    public static function all(): array
    {
        return array_values(self::map());
    }
}
