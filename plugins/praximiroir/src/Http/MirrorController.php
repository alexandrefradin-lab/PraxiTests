<?php

namespace Praxis\Plugins\PraxiMiroir\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Plugins\PraxiMiroir\Models\MirrorExercise;
use Praxis\Plugins\PraxiMiroir\Models\MirrorProgress;
use Praxis\Plugins\PraxiMiroir\Services\MirrorJourneyService;

class MirrorController extends Controller
{
    public function __construct(
        protected MirrorJourneyService $journeys,
        protected GamificationEngine   $gamification,
        protected RewardCatalog        $rewards,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if (! $this->rewards->isRouteUnlocked('praximiroir.index', $user)) {
            $reward = $this->rewards->rewardForRoute('praximiroir.index');
            $seuil  = $reward['threshold'] ?? null;

            return redirect()->route('treasure.index')->with(
                'error',
                $seuil
                    ? "Ce trésor est encore scellé. Il se révèle à {$seuil} Éclats."
                    : "Ce trésor est encore scellé."
            );
        }

        $journey  = $this->journeys->journeyFor($user);
        $current  = $this->journeys->currentDay($journey);

        $progress = MirrorProgress::forUser($user->id)
            ->get()
            ->keyBy('day_index');

        $exercises = MirrorExercise::active()->ordered()->get()->map(function ($e) use ($journey, $current, $progress) {
            $unlocked = $this->journeys->isUnlocked($journey, $e->day_index);
            $pr       = $progress->get($e->day_index);

            return [
                'day'          => $e->day_index,
                'bloc'         => $e->bloc,
                'title'        => $e->title,
                'summary'      => $e->summary,
                'duration_min' => $e->duration_min,
                'icon'         => $e->icon,
                'unlocked'     => $unlocked,
                'completed'    => $pr?->completed_at !== null,
                'is_today'     => $e->day_index === $current,
                'days_left'    => $this->journeys->daysUntilUnlock($journey, $e->day_index),
            ];
        });

        $completed = $progress->whereNotNull('completed_at')->count();

        return Inertia::render('PraxiMiroirIndex', [
            'exercises'  => $exercises,
            'currentDay' => $current,
            'totalDays'  => MirrorJourneyService::TOTAL_DAYS,
            'completed'  => $completed,
            'streak'     => $this->journeys->streakFor($user),
        ]);
    }

    public function show(Request $request, int $day)
    {
        $user     = $request->user();
        $journey  = $this->journeys->journeyFor($user);
        $exercise = MirrorExercise::active()->where('day_index', $day)->firstOrFail();

        abort_unless(
            $this->journeys->isUnlocked($journey, $day),
            403,
            'Cet exercice se débloquera dans '
                . $this->journeys->daysUntilUnlock($journey, $day) . ' jour(s).'
        );

        $pr = MirrorProgress::forUser($user->id)
            ->where('day_index', $day)
            ->first();

        return Inertia::render('PraxiMiroirExercise', [
            'exercise' => [
                'day'          => $exercise->day_index,
                'bloc'         => $exercise->bloc,
                'title'        => $exercise->title,
                'summary'      => $exercise->summary,
                'body'         => $exercise->body,
                'prompt'       => $exercise->prompt,
                'duration_min' => $exercise->duration_min,
                'icon'         => $exercise->icon,
            ],
            'state' => [
                'completed'  => $pr?->completed_at !== null,
                'reflection' => $pr?->reflection,
                'felt_score' => $pr?->felt_score,
            ],
            'nav' => [
                'prev' => $day > 1 ? $day - 1 : null,
                'next' => ($day < MirrorJourneyService::TOTAL_DAYS && $this->journeys->isUnlocked($journey, $day + 1))
                    ? $day + 1
                    : null,
            ],
            'eclatsPerExercise' => MirrorJourneyService::ECLATS_PER_EXERCISE,
        ]);
    }

    public function complete(Request $request, int $day)
    {
        $user     = $request->user();
        $journey  = $this->journeys->journeyFor($user);
        $exercise = MirrorExercise::active()->where('day_index', $day)->firstOrFail();

        abort_unless($this->journeys->isUnlocked($journey, $day), 403);

        $data = $request->validate([
            'reflection' => ['nullable', 'string', 'max:5000'],
            'felt_score' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $pr = MirrorProgress::firstOrNew([
            'user_id'   => $user->id,
            'day_index' => $day,
        ]);

        $firstTime = $pr->completed_at === null;

        $pr->completed_at = $pr->completed_at ?? now();
        $pr->reflection   = $data['reflection'] ?? $pr->reflection;
        $pr->felt_score   = $data['felt_score'] ?? $pr->felt_score;

        if ($firstTime && ! $pr->eclats_awarded) {
            $this->gamification->awardXp(
                $user,
                MirrorJourneyService::ECLATS_PER_EXERCISE,
                'praximiroir.exercise_done',
                null,
                ['day' => $day, 'title' => $exercise->title],
                false,
            );
            $pr->eclats_awarded = true;
        }

        $pr->save();

        return back()->with(
            'success',
            $firstTime
                ? 'Exercice accompli ! +' . MirrorJourneyService::ECLATS_PER_EXERCISE . ' Éclats.'
                : 'Réflexion mise à jour.'
        );
    }
}
