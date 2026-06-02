<?php

namespace App\Jobs;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\AI\Services\CvExtractionService;

class ExtractCvDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $profileId) {}

    public function handle(CvExtractionService $svc): void
    {
        $profile = Profile::find($this->profileId);
        if (!$profile) {
            logger()->warning("ExtractCvDataJob: Profile {$this->profileId} introuvable, extraction ignorée.");
            return;
        }
        $svc->structureProfile($profile);
    }
}
