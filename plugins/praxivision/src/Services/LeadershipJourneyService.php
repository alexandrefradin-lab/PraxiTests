<?php

namespace Praxis\Plugins\PraxiVision\Services;

use Praxis\Core\Journey\DailyJourneyService;
use Praxis\Plugins\PraxiVision\Models\VisionJourney;
use Praxis\Plugins\PraxiVision\Models\VisionPracticeProgress;

/**
 * Cadence « une pratique de leadership par jour » — 60 jours.
 *
 * Toute la mécanique (création du parcours, jour courant, déblocage temporel,
 * streak) vit dans le service mutualisé Praxis\Core\Journey\DailyJourneyService.
 * Ne restent ici que l'identité du plugin : ses modèles, sa table, sa durée
 * et son barème d'Éclats.
 */
class LeadershipJourneyService extends DailyJourneyService
{
    public const TOTAL_DAYS = 60;
    public const ECLATS_PER_PRACTICE = 20;

    protected function journeyModel(): string
    {
        return VisionJourney::class;
    }

    protected function progressModel(): string
    {
        return VisionPracticeProgress::class;
    }

    protected function progressTable(): string
    {
        return 'vision_practice_progress';
    }

    public function totalDays(): int
    {
        return self::TOTAL_DAYS;
    }
}
