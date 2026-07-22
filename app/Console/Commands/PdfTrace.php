<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Praxis\Core\Protection\DocumentWatermark;

/**
 * Identifie le compte à l'origine d'un rapport PDF qui a fuité.
 *
 * Le code de traçage figure en pied de page du document (format XXXX-XXXX-XXXX).
 * L'identifiant de document se lit dans le nom du fichier ou dans le journal
 * de téléchargement : « results:<id d'attempt> » ou « grimoire:<id> ».
 *
 * Exemple :
 *   php artisan praxiquest:pdf:trace 3F1A-90BC-77DE --document=results:412
 */
class PdfTrace extends Command
{
    protected $signature = 'praxiquest:pdf:trace
                            {code : Code de traçage lu en pied de page du PDF}
                            {--document= : Identifiant du document (ex. results:412)}';

    protected $description = 'Retrouve le compte à l\'origine d\'un rapport PDF diffusé hors cadre';

    public function handle(DocumentWatermark $watermark): int
    {
        $document = $this->option('document');

        if (! $document) {
            $this->error('--document est requis : le code seul ne suffit pas, il dépend du document.');
            $this->line('Il se lit dans le journal de téléchargement (laravel.log, « PDF téléchargé »).');

            return self::FAILURE;
        }

        $this->line('Balayage des comptes…');

        $user = $watermark->resolve($this->argument('code'), $document);

        if ($user === null) {
            $this->warn('Aucun compte ne correspond à ce code pour ce document.');
            $this->line('Vérifie le document, ou le fait que APP_KEY n\'ait pas été changée depuis l\'édition du PDF.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->error('Fuite imputée');
        $this->table(['Champ', 'Valeur'], [
            ['Compte', '#' . $user->id],
            ['Nom', $user->name],
            ['E-mail', $user->email],
            ['Document', $document],
            ['Code', strtoupper($this->argument('code'))],
        ]);
        $this->line('Une alerte de type pdf_leak a été consignée dans protection_alerts.');

        return self::SUCCESS;
    }
}
