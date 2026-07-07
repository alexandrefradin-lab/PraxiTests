<?php

namespace Praxis\Plugins\PraxiBoost\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\DailyTip\DailyTipService;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Plugins\PraxiBoost\Models\DevExercise;
use Praxis\Plugins\PraxiBoost\Models\DevExerciseProgress;
use Praxis\Plugins\PraxiBoost\Services\ExerciseUnlocker;

class ExerciseController extends Controller
{
    public function __construct(
        protected GamificationEngine $gamification,
        protected ExerciseUnlocker $unlocker,
        protected RewardCatalog $rewards,
        protected DailyTipService $dailyTips,
    ) {}

    public function index(Request $request)
    {
        $user  = $request->user();

        // Gating « cadeau » : la mini-app elle-même est un trésor (palier d'Éclats).
        if (! $this->rewards->isRouteUnlocked('praxiboost.index', $user)) {
            $reward = $this->rewards->rewardForRoute('praxiboost.index');
            $seuil  = $reward['threshold'] ?? null;

            return redirect()->route('treasure.index')->with(
                'error',
                $seuil
                    ? \App\Support\Parcours::sealedMessage($seuil)
                    : (\App\Support\Parcours::isCorporate() ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé.")
            );
        }

        $total = $this->gamification->totalEclats($user);

        // S'assure que les paliers déjà atteints sont enregistrés.
        $this->unlocker->syncFor($user);

        $progress = DevExerciseProgress::forUser($user->id)
            ->get()
            ->keyBy('exercise_slug');

        $exercises = DevExercise::active()->ordered()->get()->map(function ($ex) use ($total, $progress) {
            $p = $progress->get($ex->slug);
            $unlocked = $total >= $ex->threshold_eclats;

            return [
                'slug'             => $ex->slug,
                'title'            => $ex->title,
                'category'         => $ex->category,
                'summary'          => $ex->summary,
                'duration_min'     => $ex->duration_min,
                'icon'             => $ex->icon,
                'threshold_eclats' => $ex->threshold_eclats,
                'unlocked'         => $unlocked,
                'completed'        => $p?->completed_at !== null,
                'remaining'        => max(0, $ex->threshold_eclats - $total),
            ];
        });

        return Inertia::render('PraxiBoostIndex', [
            'appDescription' => $this->rewards->descriptionFor('praxiboost'),
            'exercises'     => $exercises,
            'totalEclats'   => $total,
            'dailyTip'      => $this->dailyTips->todayFor($user, 'praxiboost'),
            'tipEngagement' => $this->dailyTips->engagementFor($user, 'praxiboost'),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $user = $request->user();
        $exercise = DevExercise::active()->where('slug', $slug)->firstOrFail();

        $total = $this->gamification->totalEclats($user);
        abort_if(
            $total < $exercise->threshold_eclats,
            403,
            "Cet exercice se débloque à {$exercise->threshold_eclats} " . \App\Support\Parcours::xpName() . "."
        );

        $this->unlocker->syncFor($user);

        $p = DevExerciseProgress::forUser($user->id)
            ->where('exercise_slug', $slug)
            ->first();

        return Inertia::render('PraxiBoostExercise', [
            'exercise' => [
                'slug'         => $exercise->slug,
                'title'        => $exercise->title,
                'category'     => $exercise->category,
                'summary'      => $exercise->summary,
                'body'         => $exercise->body,
                'duration_min' => $exercise->duration_min,
                'icon'         => $exercise->icon,
            ],
            'state' => [
                'completed'   => $p?->completed_at !== null,
                'felt_score'  => $p?->felt_score,
                'notes'       => $p?->notes,
            ],
        ]);
    }

    public function complete(Request $request, string $slug)
    {
        $user = $request->user();
        $exercise = DevExercise::active()->where('slug', $slug)->firstOrFail();

        $total = $this->gamification->totalEclats($user);
        abort_if($total < $exercise->threshold_eclats, 403);

        $data = $request->validate([
            'felt_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $progress = DevExerciseProgress::firstOrNew([
            'user_id'       => $user->id,
            'exercise_slug' => $slug,
        ]);

        $firstTime = $progress->completed_at === null;

        $progress->completed_at = $progress->completed_at ?? now();
        $progress->felt_score   = $data['felt_score'] ?? $progress->felt_score;
        $progress->notes        = $data['notes'] ?? $progress->notes;

        // Garantit unlocked_at non nul sans écraser une date existante.
        $progress->unlocked_at = $progress->unlocked_at ?? now();

        // Octroi d'Éclats une seule fois par exercice (anti-doublon via eclats_awarded).
        $eclatsAwarded = 0;
        if ($firstTime && ! $progress->eclats_awarded) {
            $this->gamification->awardXp(
                $user,
                15,
                'praxiboost.exercise_done',
                null,
                ['slug' => $slug, 'title' => $exercise->title],
                false,
            );
            $progress->eclats_awarded = true;
            $eclatsAwarded = 15;
        }

        $progress->save();

        return back()->with(
            'success',
            $firstTime
                ? 'Exercice marqué comme fait.' . ($eclatsAwarded ? ' +' . $eclatsAwarded . ' ' . \App\Support\Parcours::xpName() . ' !' : '')
                : 'Ressenti mis à jour.'
        );
    }
}
