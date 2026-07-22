<?php

namespace Praxis\Core\Protection;

/**
 * Résultat immuable d'une vérification de licence.
 * Sérialisable pour permettre la mise en cache par LicenseService.
 */
class LicenseStatus
{
    public function __construct(
        public readonly string $status,
        public readonly string $host,
        public readonly ?array $claims = null,
        public readonly ?string $reason = null,
    ) {
    }

    /**
     * L'exécution est-elle autorisée ?
     * La période de grâce autorise, l'expiration franche non.
     */
    public function allowsExecution(): bool
    {
        return in_array($this->status, [
            LicenseService::STATUS_VALID,
            LicenseService::STATUS_GRACE,
        ], true);
    }

    /**
     * L'anomalie ressemble-t-elle à une copie du logiciel plutôt qu'à un
     * simple oubli d'exploitation ? Sert à distinguer, dans les journaux,
     * « licence à renouveler » de « quelqu'un a redéployé notre code ».
     */
    public function looksLikeCopy(): bool
    {
        return in_array($this->status, [
            LicenseService::STATUS_FORGED,
            LicenseService::STATUS_DOMAIN,
        ], true);
    }

    public function licensee(): ?string
    {
        return $this->claims['licensee'] ?? null;
    }

    public function expiresAt(): ?string
    {
        return $this->claims['expires_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'host'   => $this->host,
            'claims' => $this->claims,
            'reason' => $this->reason,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['status'] ?? LicenseService::STATUS_MALFORMED,
            $data['host'] ?? '',
            $data['claims'] ?? null,
            $data['reason'] ?? null,
        );
    }
}
