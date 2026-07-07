<?php

namespace Praxis\Plugins\PraxiLead\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Plugins\PraxiLead\Models\MgmtPractice;
use Praxis\Plugins\PraxiLead\Models\MgmtPracticeProgress;
use Praxis\Plugins\PraxiLead\Services\JourneyService;

class PracticeController extends Controller
{
    public function __construct(
        protected JourneyService $journeys,
        protected GamificationEngine $gamification,
        protected RewardCatalog $rewards,
    ) {}

    public function index(Request $request)
    {
        $user    = $request->user();

        // Gating Éclats : la mini-app est un trésor (palier 700 Éclats).
        if (! $this->rewards->isRouteUnlocked('praxilead.index', $user)) {
            $reward = $this->rewards->rewardForRoute('praxilead.index');
            $seuil  = $reward['threshold'] ?? null;

            return redirect()->route('treasure.index')->with(
                'error',
                $seuil
                    ? \App\Support\Parcours::sealedMessage($seuil)
                    : (\App\Support\Parcours::isCorporate() ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé.")
            );
        }

        $journey = $this->journeys->journeyFor($user);
        $current = $this->journeys->currentDay($journey);

        $progress = MgmtPracticeProgress::forUser($user->id)
            ->get()
            ->keyBy('day_index');

        $practices = MgmtPractice::active()->ordered()->get()->map(function ($p) use ($journey, $current, $progress) {
            $unlocked = $this->journeys->isUnlocked($journey, $p->day_index);
            $pr = $progress->get($p->day_index);

            return [
                'day'          => $p->day_index,
                'theme'        => $p->theme,
                'title'        => $p->title,
                'summary'      => $p->summary,
                'duration_min' => $p->duration_min,
                'icon'         => $p->icon,
                'unlocked'     => $unlocked,
                'completed'    => $pr?->completed_at !== null,
                'is_today'     => $p->day_index === $current,
                'days_left'    => $this->journeys->daysUntilUnlock($journey, $p->day_index),
            ];
        });

        $completed = $progress->whereNotNull('completed_at')->count();

        return Inertia::render('PraxiLeadIndex', [
            'appDescription' => $this->rewards->descriptionFor('praxilead'),
            'practices'  => $practices,
            'currentDay' => $current,
            'totalDays'  => JourneyService::TOTAL_DAYS,
            'completed'  => $completed,
            'streak'     => $this->journeys->streakFor($user),
        ]);
    }

    public function show(Request $request, int $day)
    {
        $user     = $request->user();
        $journey  = $this->journeys->journeyFor($user);
        $practice = MgmtPractice::active()->where('day_index', $day)->firstOrFail();

        abort_unless(
            $this->journeys->isUnlocked($journey, $day),
            403,
            'Cette pratique se débloquera dans '
                . $this->journeys->daysUntilUnlock($journey, $day) . ' jour(s).'
        );

        $pr = MgmtPracticeProgress::forUser($user->id)
            ->where('day_index', $day)
            ->first();

        return Inertia::render('PraxiLeadPractice', [
            'practice' => [
                'day'             => $practice->day_index,
                'theme'           => $practice->theme,
                'title'           => $practice->title,
                'summary'         => $practice->summary,
                'body'            => $practice->body,
                'micro_challenge' => $practice->micro_challenge,
                'duration_min'    => $practice->duration_min,
                'icon'            => $practice->icon,
            ],
            'state' => [
                'completed'  => $pr?->completed_at !== null,
                'felt_score' => $pr?->felt_score,
                'notes'      => $pr?->notes,
            ],
            'nav' => [
                'prev' => $day > 1 ? $day - 1 : null,
                'next' => ($day < JourneyService::TOTAL_DAYS && $this->journeys->isUnlocked($journey, $day + 1))
                    ? $day + 1
                    : null,
            ],
            'eclatsPerPractice' => JourneyService::ECLATS_PER_PRACTICE,
        ]);
    }

    public function complete(Request $request, int $day)
    {
        $user     = $request->user();
        $journey  = $this->journeys->journeyFor($user);
        $practice = MgmtPractice::active()->where('day_index', $day)->firstOrFail();

        abort_unless($this->journeys->isUnlocked($journey, $day), 403);

        $data = $request->validate([
            'felt_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $pr = MgmtPracticeProgress::firstOrNew([
            'user_id'   => $user->id,
            'day_index' => $day,
        ]);

        $firstTime = $pr->completed_at === null;

        $pr->completed_at = $pr->completed_at ?? now();
        $pr->felt_score   = $data['felt_score'] ?? $pr->felt_score;
        $pr->notes        = $data['notes'] ?? $pr->notes;

        // Octroi d'Éclats une seule fois par pratique.
        if ($firstTime && ! $pr->eclats_awarded) {
            $this->gamification->awardXp(
                $user,
                JourneyService::ECLATS_PER_PRACTICE,
                'praxilead.practice_done',
                null,
                ['day' => $day, 'title' => $practice->title],
                false,
            );
            $pr->eclats_awarded = true;
        }

        $pr->save();

        return back()->with(
            'success',
            $firstTime
                ? 'Pratique appliquée ! +' . JourneyService::ECLATS_PER_PRACTICE . ' ' . \App\Support\Parcours::xpName() . '.'
                : 'Ressenti mis à jour.'
        );
    }
}
