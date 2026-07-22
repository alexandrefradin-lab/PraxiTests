<?php

namespace Praxis\Core\Protection;

use App\Models\ProtectionAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Détection d'aspiration du contenu protégé (questionnaires, barèmes,
 * restitutions, pistes métiers).
 *
 * Le throttle Laravel des routes limite déjà les écritures ; ce garde-fou
 * traite le cas différent de la LECTURE massive : un compte gratuit qui
 * parcourt méthodiquement tout le catalogue pour le recopier.
 *
 * Deux signaux, du moins au plus coûteux :
 *   1. l'outillage s'annonce (curl, scrapy, headless…) → blocage immédiat ;
 *   2. la cadence dépasse ce qu'un humain peut lire → blocage temporisé.
 *
 * On compte par compte authentifié en priorité, par IP sinon : un aspirateur
 * qui tourne les IP mais garde sa session reste attrapé.
 */
class ScrapingGuard
{
    /** Le demandeur est-il actuellement sous blocage ? */
    public function isBlocked(Request $request): bool
    {
        return Cache::has($this->blockKey($this->identity($request)));
    }

    /**
     * Enregistre un accès au contenu protégé et indique s'il faut sévir.
     *
     * @return string|null Motif du blocage, ou null si l'accès est normal.
     */
    public function inspect(Request $request): ?string
    {
        if (! config('protection.scraping.enabled')) {
            return null;
        }

        $identity = $this->identity($request);

        if (Cache::has($this->blockKey($identity))) {
            return 'Accès suspendu à la suite d\'une consultation anormale du contenu.';
        }

        if ($agent = $this->matchedTool($request)) {
            $this->block($identity, $request, "Outil d'aspiration détecté ({$agent}).", [
                'matched_agent' => $agent,
            ]);

            return "Outil d'aspiration détecté.";
        }

        $window = max(1, (int) config('protection.scraping.window_minutes', 10));
        $max    = max(1, (int) config('protection.scraping.max_hits', 120));

        // Un user-agent absent est louche — aucun navigateur n'en émet — mais
        // certains proxys d'entreprise le suppriment. On ne bloque donc pas
        // dessus : on resserre seulement le seuil de cadence.
        if ((string) $request->userAgent() === '') {
            $max = max(1, (int) ceil($max / 4));
        }

        $hits = $this->increment($identity, $window);

        if ($hits > $max) {
            $this->block($identity, $request, "Cadence anormale : {$hits} pages de contenu en {$window} min.", [
                'hits'           => $hits,
                'window_minutes' => $window,
                'threshold'      => $max,
            ]);

            return 'Cadence de consultation anormale.';
        }

        return null;
    }

    /**
     * Compteur sur fenêtre glissante approchée : un seau par tranche de
     * `window` minutes, avec expiration automatique. Suffisamment précis pour
     * la détection, sans stockage par requête.
     */
    private function increment(string $identity, int $windowMinutes): int
    {
        $bucket = (int) floor(now()->timestamp / ($windowMinutes * 60));
        $key    = "protection:scraping:{$identity}:{$bucket}";

        // add() pose la clé avec son TTL au premier passage ; increment() ne
        // sait pas définir d'expiration et laisserait la clé à vie.
        Cache::add($key, 0, now()->addMinutes($windowMinutes * 2));

        return (int) Cache::increment($key);
    }

    private function block(string $identity, Request $request, string $summary, array $context): void
    {
        $minutes = max(1, (int) config('protection.scraping.block_minutes', 60));

        Cache::put($this->blockKey($identity), true, now()->addMinutes($minutes));

        ProtectionAlert::raise(
            ProtectionAlert::TYPE_SCRAPING,
            $summary,
            $context + [
                'path'           => $request->path(),
                'mode'           => config('protection.scraping.mode'),
                'block_minutes'  => $minutes,
            ],
            'critical',
        );
    }

    /** User-agent correspondant à un outil d'aspiration connu, le cas échéant. */
    private function matchedTool(Request $request): ?string
    {
        $agent = strtolower((string) $request->userAgent());

        if ($agent === '') {
            return null;
        }

        foreach ((array) config('protection.scraping.blocked_agents', []) as $needle) {
            if (str_contains($agent, strtolower($needle))) {
                return $needle;
            }
        }

        return null;
    }

    /** Identité de comptage : compte authentifié, sinon IP. */
    private function identity(Request $request): string
    {
        if ($user = $request->user()) {
            return 'u' . $user->id;
        }

        return 'ip' . sha1((string) $request->ip());
    }

    private function blockKey(string $identity): string
    {
        return "protection:scraping:blocked:{$identity}";
    }
}
