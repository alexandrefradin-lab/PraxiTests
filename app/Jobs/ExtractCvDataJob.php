<?php

namespace App\Jobs;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\AI\Services\CvExtractionService;

// #7 — ShouldBeUnique empêche les doublons d'appels OpenAI (double-soumission CV).
class ExtractCvDataJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $profileId) {}

    /** Clé d'unicité : un seul job d'extraction par profil en attente/exécution. */
    public function uniqueId(): string
    {
        return "cv_extract_{$this->profileId}";
    }

    public function handle(CvExtractionService $svc): void
    {
        $profile = Profile::find($this->profileId);
        if (!$profile) {
            logger()->warning("ExtractCvDataJob: Profile {$this->profileId} introuvable, extraction ignorée.");
            return;
        }

        // Guard DB : si le CV est déjà structuré, on ne rappelle pas OpenAI.
        // Protège les queues sync (OVH) et les soumissions en double.
        if ($profile->cv_structured) {
            logger()->info("ExtractCvDataJob: CV déjà structuré pour le profil #{$this->profileId}, skip.");
            return;
        }

        $svc->structureProfile($profile);
    }
}
