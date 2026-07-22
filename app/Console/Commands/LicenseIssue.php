<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Praxis\Core\Protection\LicenseService;

/**
 * Émet un jeton de licence signé pour un ou plusieurs domaines.
 *
 * À exécuter sur ta machine (la clé privée n'a rien à faire sur le serveur).
 * Le jeton produit se colle dans le .env du serveur :
 *   PRAXIQUEST_LICENSE_KEY="<jeton>"
 *
 * Exemple :
 *   php artisan praxiquest:license:issue \
 *       --domain=praxiquest.fr --domain='*.praxiquest.fr' \
 *       --licensee="Praxis Accompagnement" --days=730 \
 *       --private-key=~/coffre/praxiquest-license.pem
 */
class LicenseIssue extends Command
{
    protected $signature = 'praxiquest:license:issue
                            {--domain=* : Domaine autorisé (répétable, joker *.exemple.fr accepté)}
                            {--licensee= : Nom du titulaire de la licence}
                            {--days=365 : Durée de validité en jours}
                            {--edition=saas : Édition (saas, on-premise, evaluation…)}
                            {--private-key= : Chemin du fichier de clé privée PEM}
                            {--id= : Identifiant de licence (généré si omis)}';

    protected $description = 'Émet un jeton de licence PraxiQuest signé pour un domaine donné';

    public function handle(): int
    {
        $domains = array_values(array_filter($this->option('domain')));

        if ($domains === []) {
            $this->error('Au moins un --domain est requis.');

            return self::FAILURE;
        }

        $keyPath = $this->option('private-key');

        if (! $keyPath || ! is_readable($keyPath)) {
            $this->error('Clé privée introuvable ou illisible : ' . ($keyPath ?: '--private-key manquant'));
            $this->line('Génère-la d\'abord avec : php artisan praxiquest:license:keygen');

            return self::FAILURE;
        }

        $days = max(1, (int) $this->option('days'));

        $claims = [
            'v'          => 1,
            'id'         => $this->option('id') ?: 'PQ-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'licensee'   => $this->option('licensee') ?: 'Praxis Accompagnement',
            'edition'    => $this->option('edition'),
            'domains'    => $domains,
            'issued_at'  => now()->toDateString(),
            'expires_at' => now()->addDays($days)->toDateString(),
        ];

        try {
            $token = LicenseService::sign($claims, (string) file_get_contents($keyPath));
        } catch (\Throwable $e) {
            $this->error('Signature impossible : ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Licence émise');
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Identifiant', $claims['id']],
                ['Titulaire', $claims['licensee']],
                ['Édition', $claims['edition']],
                ['Domaines', implode(', ', $domains)],
                ['Expire le', $claims['expires_at']],
            ]
        );

        $this->newLine();
        $this->line('À coller dans le .env du serveur :');
        $this->newLine();
        $this->line('PRAXIQUEST_LICENSE_KEY="' . $token . '"');
        $this->newLine();
        $this->comment('Puis sur le serveur : php artisan config:cache');

        return self::SUCCESS;
    }
}
