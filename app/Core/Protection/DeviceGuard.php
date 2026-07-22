<?php

namespace Praxis\Core\Protection;

use App\Models\ProtectionAlert;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Détection du partage et de la revente d'accès professionnels.
 *
 * Un organisme qui paie une licence et fait passer les tests de dix structures
 * est la fuite de revenu la plus courante d'un SaaS B2B — et une forme de copie
 * au même titre que le vol de code : le produit est dupliqué, pas facturé.
 *
 * Deux indices convergents :
 *   - le nombre d'APPAREILS distincts sur la fenêtre d'observation ;
 *   - le nombre de RÉSEAUX distincts (préfixe /24) sur 24 h.
 * L'un seul se justifie souvent (renouvellement de parc, télétravail) ; les
 * deux ensemble décrivent un compte qui circule.
 *
 * RGPD : l'empreinte est un condensat non réversible du user-agent et de la
 * langue, salé par APP_KEY. Aucune donnée nouvelle n'est collectée — seulement
 * ce que le navigateur transmet déjà — et elle sert la sécurité du compte, ce
 * qui relève de l'intérêt légitime. À mentionner dans la politique de
 * confidentialité, avec la durée de conservation (fenêtre d'observation).
 */
class DeviceGuard
{
    /**
     * Enregistre l'appareil courant et évalue le risque de partage.
     *
     * @return string|null Motif de l'anomalie, ou null si le compte est sain.
     */
    public function inspect(Request $request): ?string
    {
        if (! config('protection.sharing.enabled')) {
            return null;
        }

        $user = $request->user();

        if (! $user instanceof User || ! $this->isWatched($user)) {
            return null;
        }

        $device = $this->touch($user, $request);

        // touch() renvoie null quand l'appareil a déjà été vu très récemment :
        // rien de neuf à évaluer, on évite la requête d'agrégation.
        if ($device === null) {
            return null;
        }

        return $this->evaluate($user, $request);
    }

    /**
     * Met à jour (ou crée) la ligne d'appareil. Limité à une écriture toutes
     * les `touch_interval_minutes` par appareil pour ne pas transformer chaque
     * requête HTTP en UPDATE.
     */
    private function touch(User $user, Request $request): ?UserDevice
    {
        $fingerprint = $this->fingerprint($request);
        $throttleKey = "protection:device:touched:{$user->id}:{$fingerprint}";

        if (Cache::has($throttleKey)) {
            return null;
        }

        Cache::put($throttleKey, true, now()->addMinutes(
            (int) config('protection.sharing.touch_interval_minutes', 15)
        ));

        $now = now();
        $ip  = (string) $request->ip();

        $device = UserDevice::firstOrNew([
            'user_id'     => $user->id,
            'fingerprint' => $fingerprint,
        ]);

        $device->label        = $this->label($request);
        $device->last_ip      = $ip;
        $device->last_network = $this->network($ip);
        $device->last_seen_at = $now;
        $device->hits         = ($device->hits ?? 0) + 1;

        if ($device->first_seen_at === null) {
            $device->first_seen_at = $now;
        }

        $device->save();

        return $device;
    }

    /** Compare l'activité du compte aux plafonds configurés. */
    private function evaluate(User $user, Request $request): ?string
    {
        $windowDays = max(1, (int) config('protection.sharing.window_days', 30));
        $maxDevices = max(1, (int) config('protection.sharing.max_devices', 5));
        $maxNetworks = max(1, (int) config('protection.sharing.max_networks_per_day', 8));

        $devices = UserDevice::where('user_id', $user->id)->recent($windowDays)->count();

        $networks = UserDevice::where('user_id', $user->id)
            ->where('last_seen_at', '>=', now()->subDay())
            ->distinct()
            ->count('last_network');

        if ($devices <= $maxDevices && $networks <= $maxNetworks) {
            return null;
        }

        $summary = $devices > $maxDevices
            ? "Compte professionnel utilisé depuis {$devices} appareils distincts sur {$windowDays} jours (plafond : {$maxDevices})."
            : "Compte professionnel utilisé depuis {$networks} réseaux distincts en 24 h (plafond : {$maxNetworks}).";

        ProtectionAlert::raise(
            ProtectionAlert::TYPE_SHARING,
            $summary,
            [
                'devices'      => $devices,
                'networks_24h' => $networks,
                'max_devices'  => $maxDevices,
                'max_networks' => $maxNetworks,
                'window_days'  => $windowDays,
                'mode'         => config('protection.sharing.mode'),
            ],
            'critical',
            $user->id,
        );

        return $summary;
    }

    /**
     * Empreinte d'appareil : condensat du user-agent et des langues acceptées,
     * salé par APP_KEY. Volontairement grossière — elle doit survivre à un
     * changement d'IP (télétravail) sans identifier une personne.
     */
    public function fingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            (string) $request->userAgent(),
            (string) $request->header('Accept-Language'),
            (string) config('app.key'),
        ]));
    }

    /** Préfixe /24 en IPv4, /48 en IPv6 — « le réseau » plutôt que « la machine ». */
    private function network(string $ip): ?string
    {
        if ($ip === '') {
            return null;
        }

        if (str_contains($ip, ':')) {
            return implode(':', array_slice(explode(':', $ip), 0, 3)) . '::';
        }

        $parts = explode('.', $ip);

        return count($parts) === 4
            ? "{$parts[0]}.{$parts[1]}.{$parts[2]}.0"
            : null;
    }

    /** Libellé lisible pour l'écran d'administration. */
    private function label(Request $request): string
    {
        $agent = (string) $request->userAgent();

        $browser = match (true) {
            str_contains($agent, 'Edg/')     => 'Edge',
            str_contains($agent, 'OPR/')     => 'Opera',
            str_contains($agent, 'Chrome/')  => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/')  => 'Safari',
            default                          => 'Navigateur inconnu',
        };

        $os = match (true) {
            str_contains($agent, 'Windows')  => 'Windows',
            str_contains($agent, 'Android')  => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Mac OS')   => 'macOS',
            str_contains($agent, 'Linux')    => 'Linux',
            default                          => 'Système inconnu',
        };

        return "{$browser} / {$os}";
    }

    private function isWatched(User $user): bool
    {
        $roles = (array) config('protection.sharing.watched_roles', []);

        // Le personnel de l'éditeur intervient depuis partout : l'exempter
        // évite une alerte permanente qui finirait par être ignorée.
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return false;
        }

        return $roles !== [] && $user->hasAnyRole($roles);
    }
}
