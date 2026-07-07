<?php

namespace Praxis\Core\Library\Http;

use App\Http\Controllers\Controller;
use App\Models\LibraryExerciseProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\DailyTip\DailyTipService;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Core\Library\ExerciseLibrary;

/**
 * Controller cœur des bibliothèques d'exercices (Salle du Trésor).
 *
 * Une seule implémentation sert les 5 mini-apps. Le plugin est passé en
 * paramètre de route (défaut figé par chaque plugin, ex. `praxispeak.index`),
 * ce qui permet à `reward.entry_route` de pointer vers une route nommée sans
 * argument.
 *
 * Gating : l'app entière est un « cadeau » de la Salle du Trésor, débloqué par
 * paliers d'Éclats cumulés (cf. RewardCatalog). Une fois l'app débloquée, tous
 * ses exercices sont accessibles (pas de sous-paliers).
 */
class LibraryController extends Controller
{
    public function __construct(
        protected ExerciseLibrary $library,
        protected RewardCatalog $rewards,
        protected DailyTipService $dailyTips,
    ) {}

    public function index(Request $request, string $plugin)
    {
        abort_unless($this->library->has($plugin), 404);

        if ($redirect = $this->sealed($request, $plugin)) {
            return $redirect;
        }

        $user = $request->user();
        $cfg  = $this->library->config($plugin);

        $done = LibraryExerciseProgress::query()
            ->where('user_id', $user->id)
            ->where('plugin_slug', $plugin)
            ->whereNotNull('completed_at')
            ->pluck('exercise_id')
            ->all();

        $exercises = array_map(fn (array $e) => [
            'id'           => $e['id'],
            'title'        => $e['title'],
            'category'     => $e['category'],
            'duration_min' => $e['duration_min'],
            'summary'      => $e['summary'],
            'icon'         => $e['icon'],
            'has_quiz'     => $e['quiz'] !== null,
            'completed'    => in_array($e['id'], $done, true),
        ], $this->library->exercises($plugin));

        return Inertia::render('Library/Index', [
            'plugin'         => $plugin,
            'app'            => [
                'title'       => $cfg['title'] ?? $plugin,
                'subtitle'    => $cfg['subtitle'] ?? null,
                'icon'        => $cfg['icon'] ?? null,
                'description' => $this->rewards->descriptionFor($plugin),
            ],
            'exercises'      => $exercises,
            'completedCount' => count($done),
            'dailyTip'       => $this->dailyTips->todayFor($user, $plugin),
            'tipEngagement'  => $this->dailyTips->engagementFor($user, $plugin),
        ]);
    }

    public function show(Request $request, string $plugin, string $exercise)
    {
        abort_unless($this->library->has($plugin), 404);

        if ($redirect = $this->sealed($request, $plugin)) {
            return $redirect;
        }

        $e = $this->library->exercise($plugin, $exercise);
        abort_if($e === null, 404);

        $cfg = $this->library->config($plugin);

        $progress = LibraryExerciseProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('plugin_slug', $plugin)
            ->where('exercise_id', $exercise)
            ->first();

        return Inertia::render('Library/Show', [
            'plugin'   => $plugin,
            'app'      => ['title' => $cfg['title'] ?? $plugin],
            'exercise' => $e,
            'state'    => [
                'completed'  => $progress?->completed_at !== null,
                'felt_score' => $progress?->felt_score,
                'notes'      => $progress?->notes,
            ],
        ]);
    }

    public function complete(Request $request, string $plugin, string $exercise)
    {
        abort_unless($this->library->has($plugin), 404);

        if ($redirect = $this->sealed($request, $plugin)) {
            return $redirect;
        }

        abort_if($this->library->exercise($plugin, $exercise) === null, 404);

        $data = $request->validate([
            'felt_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        LibraryExerciseProgress::updateOrCreate(
            [
                'user_id'     => $request->user()->id,
                'plugin_slug' => $plugin,
                'exercise_id' => $exercise,
            ],
            [
                'completed_at' => now(),
                'felt_score'   => $data['felt_score'] ?? null,
                'notes'        => $data['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Exercice marqué comme fait. Bravo !');
    }

    /**
     * Si l'app (trésor) n'est pas encore débloquée, renvoie une redirection
     * vers la Salle du Trésor ; sinon null.
     */
    protected function sealed(Request $request, string $plugin): ?RedirectResponse
    {
        $routeName = "{$plugin}.index";

        if ($this->rewards->isRouteUnlocked($routeName, $request->user())) {
            return null;
        }

        $reward = $this->rewards->rewardForRoute($routeName);
        $seuil  = $reward['threshold'] ?? null;

        return redirect()->route('treasure.index')->with(
            'error',
            $seuil
                ? \App\Support\Parcours::sealedMessage($seuil)
                : (\App\Support\Parcours::isCorporate() ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé.")
        );
    }
}
