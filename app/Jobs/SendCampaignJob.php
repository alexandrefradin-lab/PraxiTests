<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\Mailing\Services\CampaignService;

/**
 * Envoi d'une campagne email hors du cycle requête HTTP (T3 de l'audit 2026-06-21).
 *
 * Sur OVH avec QUEUE_CONNECTION=sync, ce job s'exécute en ligne (comportement
 * identique à l'ancien appel direct, zéro régression). Dès que la queue passe en
 * `database` + cron `schedule:run`, l'envoi devient asynchrone et la requête
 * admin ne risque plus le timeout PHP/Nginx sur les grosses audiences.
 *
 * ShouldBeUnique : empêche le double-envoi d'une même campagne (double-clic).
 */
class SendCampaignJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;       // pas de re-jeu auto : un envoi partiel ne doit pas tout renvoyer
    public int $timeout = 600;   // grosses audiences

    public function __construct(public int $campaignId) {}

    public function uniqueId(): string
    {
        return "send_campaign_{$this->campaignId}";
    }

    public function handle(CampaignService $svc): void
    {
        $svc->sendCampaign($this->campaignId);
    }
}
