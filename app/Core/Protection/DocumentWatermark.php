<?php

namespace Praxis\Core\Protection;

use App\Models\ProtectionAlert;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Traçage des rapports PDF.
 *
 * Chaque document porte un code court dérivé du couple (compte, document) et
 * de APP_KEY. Deux personnes qui téléchargent le même rapport obtiennent deux
 * codes différents : si un PDF ressort là où il ne devrait pas, le code
 * désigne le compte qui l'a sorti.
 *
 * Le code est déterministe et non stocké : on le recalcule à la demande pour
 * identifier une fuite (cf. praxiquest:pdf:trace). Il n'expose ni identifiant
 * de compte ni adresse e-mail — seul le serveur, qui détient APP_KEY, peut le
 * rapprocher d'une personne.
 */
class DocumentWatermark
{
    /**
     * Code de traçage d'un document pour un compte donné.
     * Format lisible à l'œil et dictable au téléphone : XXXX-XXXX-XXXX.
     *
     * @param  string  $document  Identifiant stable du document (ex. « results:42 »).
     */
    public function code(?User $user, string $document): string
    {
        $raw = hash_hmac(
            'sha256',
            ($user?->id ?? 0) . '|' . $document,
            (string) config('app.key')
        );

        $short = strtoupper(substr($raw, 0, 12));

        return implode('-', str_split($short, 4));
    }

    /**
     * Bloc à afficher en pied de page du PDF.
     * Retourne null si le tatouage visible est désactivé.
     */
    public function stamp(?User $user, string $document): ?array
    {
        if (! config('protection.watermark.enabled')) {
            return null;
        }

        $code = $this->code($user, $document);

        return [
            'code'    => $code,
            'holder'  => $user?->name,
            'visible' => (bool) config('protection.watermark.visible', true),
            // Mention dissuasive : un lecteur qui sait le document tracé le
            // fait moins circuler. C'est l'essentiel de l'effet recherché.
            'notice'  => $user
                ? "Document nominatif édité pour {$user->name} — référence de traçage {$code}. Toute rediffusion engage la responsabilité du titulaire."
                : "Document tracé — référence {$code}.",
        ];
    }

    /** Journalise un téléchargement (qui, quoi, quand, depuis où). */
    public function logDownload(?User $user, string $document, string $code): void
    {
        if (! config('protection.watermark.log_downloads', true)) {
            return;
        }

        Log::info('PDF téléchargé', [
            'user_id'  => $user?->id,
            'document' => $document,
            'trace'    => $code,
            'ip'       => request()?->ip(),
        ]);
    }

    /**
     * Retrouve le compte à l'origine d'un code de traçage.
     *
     * Recalcule le code pour chaque compte : sans table d'index, c'est un
     * balayage, mais il ne tourne qu'en réponse à une fuite constatée.
     *
     * @return User|null Le titulaire, ou null si le code ne correspond à rien.
     */
    public function resolve(string $code, string $document): ?User
    {
        $needle = strtoupper(trim($code));
        $found  = null;

        User::withTrashed()
            ->select(['id', 'name', 'email'])
            ->chunkById(500, function ($users) use ($needle, $document, &$found) {
                foreach ($users as $user) {
                    if (hash_equals($this->code($user, $document), $needle)) {
                        $found = $user;

                        return false; // arrête le balayage
                    }
                }

                return true;
            });

        if ($found !== null) {
            ProtectionAlert::raise(
                ProtectionAlert::TYPE_PDF_LEAK,
                "Fuite de rapport PDF imputée au compte #{$found->id}.",
                ['document' => $document, 'trace' => $needle],
                'critical',
                $found->id,
            );
        }

        return $found;
    }
}
