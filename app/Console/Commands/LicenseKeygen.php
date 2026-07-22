<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Génère la paire de clés qui fonde tout le dispositif de licence.
 *
 * À exécuter UNE SEULE FOIS, sur ta machine, jamais sur le serveur.
 * - la clé PUBLIQUE se colle dans config/protection.php (elle est versionnée) ;
 * - la clé PRIVÉE se range hors du dépôt (gestionnaire de mots de passe,
 *   coffre chiffré). Qui la détient peut émettre des licences PraxiQuest.
 *
 * Régénérer la paire invalide toutes les licences déjà émises.
 */
class LicenseKeygen extends Command
{
    protected $signature = 'praxiquest:license:keygen
                            {--out= : Écrit la clé privée dans ce fichier au lieu de l\'afficher}
                            {--bits=2048 : Taille de la clé RSA}';

    protected $description = 'Génère la paire de clés RSA servant à signer les licences PraxiQuest';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refus : la clé privée ne doit jamais être générée sur le serveur de production.');
            $this->line('Exécute cette commande sur ton poste, puis ne dépose que la clé publique.');

            return self::FAILURE;
        }

        $bits = max(2048, (int) $this->option('bits'));

        $resource = openssl_pkey_new([
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            $this->error('Génération impossible : ' . (openssl_error_string() ?: 'erreur OpenSSL inconnue'));

            return self::FAILURE;
        }

        openssl_pkey_export($resource, $privateKey);
        $publicKey = openssl_pkey_get_details($resource)['key'];

        $this->newLine();
        $this->info('── CLÉ PUBLIQUE ──  à coller dans config/protection.php → license.public_key');
        $this->line($publicKey);

        if ($path = $this->option('out')) {
            file_put_contents($path, $privateKey);
            @chmod($path, 0600);
            $this->info("── CLÉ PRIVÉE ──  écrite dans {$path}");
            $this->warn('Range ce fichier hors du dépôt (coffre / gestionnaire de mots de passe) puis supprime-le du disque.');
        } else {
            $this->info('── CLÉ PRIVÉE ──  à conserver hors du dépôt, ne la perds pas');
            $this->line($privateKey);
        }

        $this->newLine();
        $this->line('Étape suivante : php artisan praxiquest:license:issue --domain=praxiquest.fr --private-key=chemin/vers/cle.pem');

        return self::SUCCESS;
    }
}
