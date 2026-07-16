<?php

namespace Praxis\Plugins\PraxiVision\Http;

use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Core\Journey\Http\DailyPracticeController;
use Praxis\Plugins\PraxiVision\Models\VisionPractice;
use Praxis\Plugins\PraxiVision\Models\VisionPracticeProgress;
use Praxis\Plugins\PraxiVision\Services\LeadershipJourneyService;

/**
 * L'Eveilleur — parcours leadership 60 jours.
 *
 * Toute la mécanique (gating Éclats, cadence quotidienne, complétion, octroi
 * d'Éclats) vit dans le contrôleur mutualisé
 * Praxis\Core\Journey\Http\DailyPracticeController. Ne restent ici que
 * l'identité du plugin ; les libellés par défaut (« Pratique intégrée ! »,
 * « Ressenti mis à jour. »…) correspondent déjà aux siens.
 */
class PracticeController extends DailyPracticeController
{
    public function __construct(
        LeadershipJourneyService $journeys,
        GamificationEngine $gamification,
        RewardCatalog $rewards,
    ) {
        parent::__construct($journeys, $gamification, $rewards);
    }

    protected function slug(): string
    {
        return 'praxivision';
    }

    protected function itemModel(): string
    {
        return VisionPractice::class;
    }

    protected function progressModel(): string
    {
        return VisionPracticeProgress::class;
    }

    protected function indexPage(): string
    {
        return 'PraxiVisionIndex';
    }

    protected function showPage(): string
    {
        return 'PraxiVisionPractice';
    }

    protected function eclatsPerItem(): int
    {
        return LeadershipJourneyService::ECLATS_PER_PRACTICE;
    }
}
