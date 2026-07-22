<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Praxis\Core\Protection\LicenseService;

/**
 * Diagnostic de la licence installée — à lancer sur le serveur après tout
 * renouvellement, ou quand l'application renvoie une 503 inattendue.
 */
class LicenseStatus extends Command
{
    protected $signature = 'praxiquest:license:status
                            {--host= : Domaine à tester (défaut : celui d\'APP_URL)}';

    protected $description = 'Affiche l\'état de la licence PraxiQuest sur cette installation';

    public function handle(LicenseService $license): int
    {
        $host = $this->option('host')
            ?: parse_url((string) config('app.url'), PHP_URL_HOST)
            ?: 'localhost';

        // Le cache masquerait un jeton fraîchement remplacé.
        $license->flush($host);
        $status = $license->status($host);

        $this->newLine();
        $this->table(
            ['Champ', 'Valeur'],
            array_filter([
                ['Contrôle actif', config('protection.license.enabled') ? 'oui' : 'non (PRAXIQUEST_LICENSE_ENFORCED=false)'],
                ['Mode', config('protection.license.mode')],
                ['Domaine testé', $host],
                ['État', $status->status],
                ['Exécution autorisée', $status->allowsExecution() ? 'oui' : 'NON'],
                ['Titulaire', $status->licensee() ?: '—'],
                ['Domaines licenciés', implode(', ', (array) ($status->claims['domains'] ?? [])) ?: '—'],
                ['Expire le', $status->expiresAt() ?: '—'],
                $status->reason ? ['Motif', $status->reason] : null,
            ])
        );

        if ($status->looksLikeCopy()) {
            $this->newLine();
            $this->error('Cette installation ne correspond pas à sa licence — vérifier s\'il s\'agit d\'une copie du code.');
        }

        return $status->allowsExecution() ? self::SUCCESS : self::FAILURE;
    }
}
