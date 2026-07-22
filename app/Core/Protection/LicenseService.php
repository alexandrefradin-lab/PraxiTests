<?php

namespace Praxis\Core\Protection;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Vérification de la licence d'exécution de PraxiQuest.
 *
 * PRINCIPE
 * Le jeton de licence est signé avec une clé privée RSA que seul l'éditeur
 * détient (jamais déposée sur le serveur, jamais dans le dépôt). Le serveur
 * ne porte que la clé publique, figée dans config/protection.php. Le jeton
 * énumère les domaines autorisés et une date d'expiration : un code copié et
 * redéployé sur `praxiquest-clone.com` n'a pas de jeton valide pour ce
 * domaine, et l'auteur de la copie ne peut pas en forger un sans la clé privée.
 *
 * CE QUE ÇA NE FAIT PAS
 * Aucune protection logicielle n'est inviolable face à quelqu'un qui possède
 * le code source : il peut toujours commenter l'appel à ce service. L'objectif
 * est de rendre la copie non triviale (elle exige de comprendre et de modifier
 * le code, ce qui caractérise la contrefaçon volontaire) et de laisser une
 * trace exploitable en cas de litige. La vraie barrière reste juridique.
 *
 * Format du jeton : base64url(payload JSON) . '.' . base64url(signature)
 */
class LicenseService
{
    public const STATUS_VALID    = 'valid';
    public const STATUS_MISSING  = 'missing';   // aucun jeton dans le .env
    public const STATUS_MALFORMED = 'malformed'; // jeton illisible
    public const STATUS_FORGED   = 'forged';    // signature invalide → contrefaçon
    public const STATUS_DOMAIN   = 'domain';    // domaine hors licence → redéploiement
    public const STATUS_EXPIRED  = 'expired';   // au-delà de la période de grâce
    public const STATUS_GRACE    = 'grace';     // expirée mais dans la tolérance

    /** Résultat mémorisé pour la durée de la requête. */
    private ?LicenseStatus $memo = null;

    /**
     * État de la licence pour l'hôte courant.
     *
     * @param  string|null  $host  Domaine à valider (défaut : hôte de la requête).
     */
    public function status(?string $host = null): LicenseStatus
    {
        $host = $this->normalizeHost($host ?? request()?->getHost() ?? 'localhost');

        if ($this->memo !== null && $this->memo->host === $host) {
            return $this->memo;
        }

        $ttl = (int) config('protection.license.cache_ttl', 300);

        // La vérification RSA coûte ~0,1 ms, mais la mise en cache évite aussi
        // de rejouer le décodage JSON sur chaque requête d'un site chargé.
        $payload = $ttl > 0
            ? Cache::remember("protection:license:{$host}", $ttl, fn () => $this->verify($host)->toArray())
            : $this->verify($host)->toArray();

        return $this->memo = LicenseStatus::fromArray($payload);
    }

    /** Raccourci : la licence autorise-t-elle l'exécution ici et maintenant ? */
    public function passes(?string $host = null): bool
    {
        return $this->status($host)->allowsExecution();
    }

    /** Vide le cache de vérification (après renouvellement du jeton). */
    public function flush(?string $host = null): void
    {
        $this->memo = null;

        if ($host !== null) {
            Cache::forget('protection:license:' . $this->normalizeHost($host));
            return;
        }

        // Sans hôte connu, on ne peut pas énumérer les clés sur tous les
        // drivers de cache : on invalide au moins l'hôte courant.
        Cache::forget('protection:license:' . $this->normalizeHost(request()?->getHost() ?? 'localhost'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Vérification
    // ──────────────────────────────────────────────────────────────────────────

    private function verify(string $host): LicenseStatus
    {
        $token = trim((string) config('protection.license.key'));

        if ($token === '') {
            return new LicenseStatus(self::STATUS_MISSING, $host, null,
                'Aucune clé de licence : PRAXIQUEST_LICENSE_KEY est absent du .env.');
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return new LicenseStatus(self::STATUS_MALFORMED, $host, null,
                'Jeton de licence malformé (format attendu : payload.signature).');
        }

        $rawPayload = $this->b64decode($parts[0]);
        $signature  = $this->b64decode($parts[1]);

        if ($rawPayload === null || $signature === null) {
            return new LicenseStatus(self::STATUS_MALFORMED, $host, null,
                'Jeton de licence illisible (base64url invalide).');
        }

        $claims = json_decode($rawPayload, true);
        if (! is_array($claims)) {
            return new LicenseStatus(self::STATUS_MALFORMED, $host, null,
                'Charge utile de licence illisible (JSON invalide).');
        }

        // La signature porte sur la charge utile brute : toute retouche des
        // domaines ou de la date d'expiration invalide le jeton.
        if (! $this->signatureIsValid($rawPayload, $signature)) {
            return new LicenseStatus(self::STATUS_FORGED, $host, $claims,
                'Signature de licence invalide — jeton contrefait ou clé publique erronée.');
        }

        if (! $this->hostIsLicensed($host, (array) ($claims['domains'] ?? []))) {
            return new LicenseStatus(self::STATUS_DOMAIN, $host, $claims,
                "Le domaine « {$host} » n'est pas couvert par cette licence.");
        }

        $expiry = $this->parseDate($claims['expires_at'] ?? null);

        if ($expiry !== null && $expiry->isPast()) {
            $graceEnd = $expiry->copy()->addDays((int) config('protection.license.grace_days', 14));

            return $graceEnd->isFuture()
                ? new LicenseStatus(self::STATUS_GRACE, $host, $claims,
                    'Licence expirée le ' . $expiry->toDateString() . ' — période de tolérance jusqu\'au ' . $graceEnd->toDateString() . '.')
                : new LicenseStatus(self::STATUS_EXPIRED, $host, $claims,
                    'Licence expirée le ' . $expiry->toDateString() . ', période de tolérance dépassée.');
        }

        return new LicenseStatus(self::STATUS_VALID, $host, $claims, null);
    }

    /** Vérifie la signature RSA-SHA256 contre la clé publique embarquée. */
    private function signatureIsValid(string $payload, string $signature): bool
    {
        $pem = $this->publicKey();

        if ($pem === null) {
            // Clé publique absente ou encore à l'état de gabarit : on ne peut
            // rien affirmer. On refuse plutôt que de valider à l'aveugle, mais
            // le mode 'warn' par défaut évite de couper la production.
            Log::warning('Licence : clé publique de vérification absente ou non renseignée dans config/protection.php.');
            return false;
        }

        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            Log::warning('Licence : clé publique illisible (PEM invalide).');
            return false;
        }

        return openssl_verify($payload, $signature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    /** Clé publique PEM, ou null si elle n'a pas encore été renseignée. */
    private function publicKey(): ?string
    {
        $pem = trim((string) config('protection.license.public_key'));

        if ($pem === '' || str_contains($pem, 'REMPLACER_PAR')) {
            return null;
        }

        return $pem;
    }

    /**
     * Le domaine est-il couvert ? Gère les jokers de sous-domaine
     * (`*.praxiquest.fr` couvre `app.praxiquest.fr` mais pas `praxiquest.fr`,
     * qu'il faut lister explicitement).
     */
    private function hostIsLicensed(string $host, array $domains): bool
    {
        foreach ($domains as $pattern) {
            $pattern = $this->normalizeHost((string) $pattern);

            if ($pattern === $host) {
                return true;
            }

            if (str_starts_with($pattern, '*.') && str_ends_with($host, substr($pattern, 1))) {
                return true;
            }
        }

        return false;
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('#^https?://#', '', $host) ?? $host;
        $host = explode('/', $host)[0];
        $host = explode(':', $host)[0];

        return ltrim($host, '.');
    }

    private function parseDate(mixed $value): ?\Illuminate\Support\Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->endOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function b64decode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Émission (poste de l'éditeur uniquement — exige la clé privée)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Signe une licence. Appelé par praxiquest:license:issue, jamais en
     * production : la clé privée ne doit pas exister sur le serveur.
     *
     * @param  array   $claims      domains, licensee, expires_at, edition…
     * @param  string  $privateKey  Clé privée RSA au format PEM.
     */
    public static function sign(array $claims, string $privateKey): string
    {
        $key = openssl_pkey_get_private($privateKey);

        if ($key === false) {
            throw new \RuntimeException('Clé privée illisible (PEM invalide ou protégé par mot de passe).');
        }

        $payload = json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            throw new \RuntimeException('Charge utile de licence non sérialisable.');
        }

        $signature = '';
        if (! openssl_sign($payload, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Échec de la signature de la licence.');
        }

        return self::b64encode($payload) . '.' . self::b64encode($signature);
    }

    private static function b64encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
