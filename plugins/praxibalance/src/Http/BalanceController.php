<?php

namespace Praxis\Plugins\PraxiBalance\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Plugins\PraxiBalance\Data\Levels;
use Praxis\Plugins\PraxiBalance\Data\Notions;
use Praxis\Plugins\PraxiBalance\Data\Prompts;
use Praxis\Plugins\PraxiBalance\Data\Tasks;
use Praxis\Plugins\PraxiBalance\Models\BalanceLevelProgress;
use Praxis\Plugins\PraxiBalance\Models\BalanceNotionProgress;
use Praxis\Plugins\PraxiBalance\Models\BalanceProfile;

/**
 * La Balance — entraînement d'attention par cartes glissées.
 *
 * Le déroulé d'une session vit côté client (le geste doit rester immédiat :
 * un aller-retour serveur par carte casserait la mesure du temps de réaction).
 * Le serveur reste maître de ce qui compte : l'ancrage des notions, la
 * validation des niveaux et l'octroi des Éclats, tous recalculés ici à partir
 * du résultat transmis en fin de session.
 */
class BalanceController extends Controller
{
    public function __construct(
        protected GamificationEngine $gamification,
        protected RewardCatalog $rewards,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Gating Éclats : la mini-app est un trésor de la Salle du Trésor.
        if ($redirect = $this->rewards->unlockRedirect('praxibalance.index', $user)) {
            return $redirect;
        }

        $profile = BalanceProfile::forUser($user->id);

        $progress = BalanceNotionProgress::forUser($user->id)->get()->keyBy('notion_id');
        $levels   = BalanceLevelProgress::forUser($user->id)->get()->keyBy('level');

        $unlocked = 1;
        foreach (Levels::all() as $level) {
            if ($levels->has($level['id']) && $levels[$level['id']]->completed_at !== null) {
                $unlocked = min($level['id'] + 1, count(Levels::all()));
            }
        }

        $due = $progress
            ->filter(fn ($p) => $p->due_session <= $profile->sessions_count)
            ->keys()
            ->all();

        return Inertia::render('PraxiBalanceIndex', [
            'appDescription' => $this->rewards->descriptionFor('praxibalance'),

            'levels' => collect(Levels::all())->map(fn (array $l) => [
                'id'         => $l['id'],
                'type'       => $l['type'],
                'title'      => $l['title'],
                'desc'       => $l['desc'],
                'rank'       => $l['rank'],
                'training'   => $l['training'] ?? null,
                'pass'       => Levels::passMark($l),
                'unlocked'   => $l['id'] <= $unlocked,
                'best_score' => $levels->get($l['id'])?->best_score,
                'completed'  => $levels->get($l['id'])?->completed_at !== null,
            ])->values(),

            'notions' => collect(Notions::all())->map(fn (array $n) => [
                'id'          => $n['id'],
                'level'       => $n['level'],
                'theme'       => $n['theme'],
                'explanation' => $n['explanation'],
                'variants'    => $n['variants'],
                'box'         => $progress->get($n['id'])?->box ?? 0,
                'variant'     => $progress->get($n['id'])?->variant_index ?? -1,
                'due'         => in_array($n['id'], $due, true),
            ])->values(),

            // Les cartes des series chronometrees. Envoyees en bloc : le tri
            // doit rester immediat, un aller-retour par carte fausserait la
            // mesure du temps de decision.
            'tasks' => Tasks::all(),
            'prompts' => [
                'power'  => Prompts::powerQuestions(),
                'resets' => Prompts::resets(),
            ],

            'profile' => [
                'sessions'   => $profile->sessions_count,
                'points'     => $profile->points,
                'mean_rt_ms' => $profile->mean_rt_ms,
                'rank'       => Levels::rankFor($levels->whereNotNull('completed_at')->count()),
                'anchor_pct' => $this->anchorPercentage($progress),
                'due_count'  => count($due),
            ],

            'config' => [
                'max_review'  => Levels::MAX_REVIEW,
                'intervals'   => BalanceNotionProgress::INTERVALS,
                'max_box'     => BalanceNotionProgress::MAX_BOX,
            ],
        ]);
    }

    /**
     * Enregistre le résultat d'une session : ancrage des notions vues,
     * validation éventuelle du niveau, puis Éclats au premier passage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // SEC : gating avant tout octroi de récompense.
        if ($redirect = $this->rewards->unlockRedirect('praxibalance.index', $user)) {
            return $redirect;
        }

        $data = $request->validate([
            'level'                  => ['required', 'integer', 'min:1', 'max:6'],
            'score'                  => ['required', 'integer', 'min:0', 'max:100'],
            'notions'                => ['array', 'max:40'],
            'notions.*.id'           => ['required', 'string', 'max:16'],
            'notions.*.correct'      => ['required', 'boolean'],
            'notions.*.variant'      => ['required', 'integer', 'min:0', 'max:9'],
            'reaction_times'         => ['array', 'max:60'],
            'reaction_times.*'       => ['integer', 'min:1', 'max:10000'],
        ]);

        $level = Levels::find($data['level']);
        abort_if($level === null, 404);

        $profile = BalanceProfile::forUser($user->id);
        $passed  = $data['score'] >= Levels::passMark($level);

        DB::transaction(function () use ($user, $data, $level, $profile, $passed, &$awarded) {
            // 1 · Ancrage des notions vues, dans l'ordre des cartes servies.
            foreach ($data['notions'] ?? [] as $seen) {
                if (Notions::find($seen['id']) === null) {
                    continue;   // identifiant inconnu : on ignore, on ne crée pas de ligne fantôme
                }

                $row = BalanceNotionProgress::firstOrNew([
                    'user_id'   => $user->id,
                    'notion_id' => $seen['id'],
                ]);

                $row->variant_index = $seen['variant'];
                $row->grade($seen['correct'], $profile->sessions_count);
                $row->save();
            }

            // 2 · La session est consommée : l'horloge de l'ancrage avance.
            $profile->sessions_count++;
            $profile->addReactionTimes($data['reaction_times'] ?? []);

            // 3 · Niveau validé : meilleur score, puis Éclats une seule fois.
            $awarded = 0;

            if ($passed) {
                $row = BalanceLevelProgress::firstOrNew([
                    'user_id' => $user->id,
                    'level'   => $level['id'],
                ]);

                $row->best_score   = max($row->best_score ?? 0, $data['score']);
                $row->completed_at = $row->completed_at ?? now();

                if (! $row->eclats_awarded) {
                    $this->gamification->awardXp(
                        $user,
                        Levels::ECLATS_PER_LEVEL,
                        'praxibalance.level_passed',
                        null,
                        ['level' => $level['id'], 'title' => $level['title']],
                        false,
                    );

                    $row->eclats_awarded = true;
                    $awarded             = Levels::ECLATS_PER_LEVEL;
                }

                $row->save();
            }

            $profile->points += $awarded;
            $profile->save();
        });

        return back()->with('praxibalance', [
            'passed'  => $passed,
            'eclats'  => $awarded ?? 0,
            'rank'    => $passed ? $level['rank'] : null,
        ]);
    }

    /**
     * Ancrage global : chaque notion pèse sa boîte sur le maximum possible.
     * Progression continue plutôt que seuil, pour que la jauge bouge dès la
     * première réussite.
     */
    protected function anchorPercentage($progress): int
    {
        $total = Notions::count() * BalanceNotionProgress::MAX_BOX;

        if ($total === 0) {
            return 0;
        }

        return (int) round($progress->sum('box') / $total * 100);
    }
}
