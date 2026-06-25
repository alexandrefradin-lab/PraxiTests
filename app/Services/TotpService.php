<?php

namespace App\Services;

/**
 * TotpService — implémentation TOTP (RFC 6238) sans dépendance externe.
 *
 * Algorithme :
 *   TOTP(K, t) = HOTP(K, floor(timestamp / 30))
 *   HOTP(K, c) = Truncate(HMAC-SHA1(K, c)) mod 10^6
 *
 * Utilisé pour le 2FA admin/pro (audit A-2).
 */
class TotpService
{
    private const DIGITS    = 6;
    private const PERIOD    = 30;       // secondes
    private const WINDOW    = 1;        // fenêtre de tolérance (±1 période)
    private const ISSUER    = 'PraxiQuest';
    private const ALGO      = 'sha1';

    // ──────────────────────────────────────────────────────────────────────────
    // Secret
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Génère un secret aléatoire en Base32 (16 caractères = 80 bits).
     */
    public static function generateSecret(): string
    {
        // 10 octets = 80 bits, encodés en Base32 = 16 caractères
        $bytes = random_bytes(10);
        return self::base32Encode($bytes);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // URI otpauth://
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Retourne l'URI otpauth:// à encoder en QR code.
     */
    public static function getUri(string $secret, string $accountLabel, string $issuer = self::ISSUER): string
    {
        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d',
            rawurlencode($issuer . ':' . $accountLabel),
            $secret,
            rawurlencode($issuer),
            strtoupper(self::ALGO),
            self::DIGITS,
            self::PERIOD,
        );
    }

    /**
     * URL vers l'image QR (api.qrserver.com — service public gratuit, sans tracking).
     */
    public static function getQrUrl(string $secret, string $accountLabel): string
    {
        $uri = self::getUri($secret, $accountLabel);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&data=' . rawurlencode($uri);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Vérification
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Vérifie un code TOTP en autorisant une fenêtre de ±WINDOW périodes.
     */
    public static function verify(string $secret, string $code, int $window = self::WINDOW): bool
    {
        $code = preg_replace('/\s+/', '', $code); // tolérer les espaces (ex : "123 456")

        if (!preg_match('/^\d{' . self::DIGITS . '}$/', $code)) {
            return false;
        }

        $timestamp = time();
        $counter   = (int) floor($timestamp / self::PERIOD);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $expected = self::hotp($secret, $counter + $offset);
            // Comparaison à temps constant pour résister aux timing attacks
            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Codes de récupération
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Génère 8 codes de récupération monoforme (format: XXXX-XXXX).
     *
     * @return array<string> codes en clair
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = sprintf(
                '%s-%s',
                strtoupper(bin2hex(random_bytes(3))),
                strtoupper(bin2hex(random_bytes(3)))
            );
        }
        return $codes;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internes — HOTP + Base32
    // ──────────────────────────────────────────────────────────────────────────

    private static function hotp(string $secret, int $counter): string
    {
        $key     = self::base32Decode($secret);
        $message = pack('J', $counter); // 64-bit big-endian unsigned

        $hash   = hash_hmac(self::ALGO, $message, $key, binary: true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code   = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    // ─── Base32 (RFC 4648) ───────────────────────────────────────────────────

    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private static function base32Encode(string $bytes): string
    {
        $output = '';
        $buffer = 0;
        $bits   = 0;

        foreach (str_split($bytes) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bits  += 8;
            while ($bits >= 5) {
                $bits   -= 5;
                $output .= self::BASE32_CHARS[($buffer >> $bits) & 0x1F];
            }
        }

        if ($bits > 0) {
            $output .= self::BASE32_CHARS[($buffer << (5 - $bits)) & 0x1F];
        }

        // Pas de padding nécessaire pour les OTP URI
        return $output;
    }

    private static function base32Decode(string $input): string
    {
        $input  = strtoupper(preg_replace('/[^A-Z2-7]/', '', $input));
        $map    = array_flip(str_split(self::BASE32_CHARS));
        $buffer = 0;
        $bits   = 0;
        $output = '';

        foreach (str_split($input) as $char) {
            if (!isset($map[$char])) continue;
            $buffer = ($buffer << 5) | $map[$char];
            $bits  += 5;
            if ($bits >= 8) {
                $bits   -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }
}
