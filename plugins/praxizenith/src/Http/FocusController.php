<?php

namespace Praxis\Plugins\PraxiZenith\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Plugins\PraxiZenith\Models\FocusExercise;
use Praxis\Plugins\PraxiZenith\Models\FocusExerciseProgress;
use Praxis\Plugins\PraxiZenith\Services\FocusJourneyService;

class FocusController extends Controller
{
    public function __construct(
        protected FocusJourneyService $journeys,
        protected GamificationEngine $gamification,
    ) {}

    public function index(Request $request)
    {
        $user    = $request->user();
        $journey = $this->journeys->journeyFor($user);
        $current = $this->journeys->currentDay($journey);

        $progress = FocusExerciseProgress::forUser($user->id)
            ->get()
            ->keyBy('day_index');

        $exercises = FocusExercise::active()->ordered()->get()->map(function ($e) use ($journey, $current, $progress) {
            $unlocked = $this->journeys->isUnlocked($journey, $e->day_index);
            $pr = $progress->get($e->day_index);

            return [
                'day'          => $e->day_index,
                'theme'        => $e->theme,
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

        return Inertia::render('PraxiZenithIndex', [
            'exercises'  => $exercises,
            'currentDay' => $current,
            'totalDays'  => FocusJourneyService::TOTAL_DAYS,
            'completed'  => $completed,
            'streak'     => $this->journeys->streakFor($user),
        ]);
    }

    public function show(Request $request, int $day)
    {
        $user     = $request->user();
        $journey  = $this->journeys->journeyFor($user);
        $exercise = FocusExercise::active()->where('day_index', $day)->firstOrFail();

        abort_unless(
            $this->journeys->isUnlocked($journey, $day),
            403,
            'Cet exercice se débloquera dans '
                . $this->journeys->daysUntilUnlock($journey, $day) . ' jour(s).'
        );

        $pr = FocusExerciseProgress::forUser($user->id)
            ->where('day_index', $day)
            ->first();

        return Inertia::render('PraxiZenithExercise', [
            'exercise' => [
                'day'             => $exercise->day_index,
                'theme'           => $exercise->theme,
                'title'           => $exercise->title,
                'summary'         => $exercise->summary,
                'body'            => $exercise->body,
                'micro_challenge' => $exercise->micro_challenge,
                'duration_min'    => $exercise->duration_min,
                'icon'            => $exercise->icon,
            ],
            'state' => [
                'completed'  => $pr?->completed_at !== null,
                'felt_score' => $pr?->felt_score,
                'notes'      => $pr?->notes,
            ],
            'nav' => [
                'prev' => $day > 1 ? $day - 1 : null,
                'next' => ($day < FocusJourneyService::TOTAL_DAYS && $this->journeys->isUnlocked($journey, $day + 1))
                    ? $day + 1
                    : null,
            ],
            'eclatsPerExercise' => FocusJourneyService::ECLATS_PER_EXERCISE,
        ]);
    }

    public function complete(Request $request, int $day)
    {
        $user     = $request->user();
        $journey  = $this->journeys->journeyFor($user);
        $exercise = FocusExercise::active()->where('day_index', $day)->firstOrFail();

        abort_unless($this->journeys->isUnlocked($journey, $day), 403);

        $data = $request->validate([
            'felt_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $pr = FocusExerciseProgress::firstOrNew([
            'user_id'   => $user->id,
            'day_index' => $day,
        ]);

        $firstTime = $pr->completed_at === null;

        $pr->completed_at = $pr->completed_at ?? now();
        $pr->felt_score   = $data['felt_score'] ?? $pr->felt_score;
        $pr->notes        = $data['notes'] ?? $pr->notes;

        // Octroi d'Éclats une seule fois par exercice.
        if ($firstTime && ! $pr->eclats_awarded) {
            $this->gamification->awardXp(
                $user,
                FocusJourneyService::ECLATS_PER_EXERCISE,
                'praxizenith.exercise_done',
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
                ? 'Exercice appliqué ! +' . FocusJourneyService::ECLATS_PER_EXERCISE . ' Éclats.'
                : 'Ressenti mis à jour.'
        );
    }
}
