<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestAttempt;
use Inertia\Inertia;
use Praxis\Core\Gamification\RewardCatalog;

class TestController extends Controller
{
    public function __construct(protected RewardCatalog $rewards) {}

    public function index()
    {
        // Les apps offertes en récompense vivent dans La Salle du Trésor :
        // on les retire de L'Armurerie pour éviter les doublons.
        $rewardSlugs = $this->rewards->testSlugs();

        $userId = auth()->id();

        $completedIds = TestAttempt::where('user_id', $userId)
            ->where('status', 'completed')
            ->pluck('test_id')
            ->flip()
            ->toArray();

        // Paywall particulier : les épreuves hors épreuve d'appel sont
        // verrouillées pour un auto-inscrit sans achat (config/b2c.php).
        $b2cLocked = \App\Support\B2c::locked(auth()->user());
        $freeSlugs = (array) config('b2c.free_test_slugs', []);

        $tests = Test::where('published', true)
            ->when($rewardSlugs, fn ($q) => $q->whereNotIn('slug', $rewardSlugs))
            ->get(['id','slug','name','description','estimated_minutes'])
            ->map(fn ($t) => array_merge($t->toArray(), [
                'completed' => isset($completedIds[$t->id]),
                'locked'    => $b2cLocked && ! in_array($t->slug, $freeSlugs, true),
            ]));

        return Inertia::render('Candidate/TestsIndex', [
            'tests' => $tests,
            'profile_complete' => auth()->user()->profile?->isComplete() ?? false,
            'b2c' => [
                'locked'      => $b2cLocked,
                'price_label' => $b2cLocked
                    ? number_format((config('b2c.products.rapport.price', 4900)) / 100, 0, ',', ' ') . ' €'
                    : null,
            ],
        ]);
    }

    public function show(Test $test)
    {
        abort_unless($test->published, 404);

        $user = auth()->user();

        // Paywall particulier : la fiche d'une épreuve verrouillée renvoie vers
        // la page de déblocage (l'écran de conversion) plutôt qu'un cul-de-sac.
        if (! \App\Support\B2c::isFreeTest($test) && \App\Support\B2c::locked($user)) {
            return redirect()->route('b2c.unlock')
                ->with('info', 'Cette épreuve fait partie du Rapport complet. Débloque ton parcours pour la lancer.');
        }

        // Gating « cadeau » : un test adossé à un reward reste scellé tant que
        // le palier d'Éclats n'est pas atteint → on renvoie vers le Trésor.
        if (! $this->rewards->isTestUnlocked($test->slug, $user)) {
            $reward = $this->rewards->rewardForTestSlug($test->slug);
            $seuil  = $reward['threshold'] ?? null;

            return redirect()->route('treasure.index')->with(
                'error',
                $seuil
                    ? \App\Support\Parcours::sealedMessage($seuil)
                    : (\App\Support\Parcours::isCorporate() ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé.")
            );
        }

        $inProgress = TestAttempt::where('user_id', $user->id)
            ->where('test_id', $test->id)
            ->where('status', 'in_progress')
            ->first();

        // Dernière tentative terminée : on renvoie son id pour le lien « Voir ma
        // Révélation » (route results.show = {attempt}). Avant, only exists() était
        // envoyé → le lien testait already_attempted.result_id (toujours undefined)
        // et ne s'affichait jamais.
        $completedAttempt = TestAttempt::where('user_id', $user->id)
            ->where('test_id', $test->id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        return Inertia::render('Candidate/TestShow', [
            'test'                 => $test->load('sections.questions'),
            'profile_complete'     => $user->profile?->isComplete() ?? false,
            'already_attempted'    => (bool) $completedAttempt,
            'completed_attempt_id' => $completedAttempt?->id,
            'attempt_in_progress'  => $inProgress ? $inProgress->only('id') : null,
        ]);
    }
}
