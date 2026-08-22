<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Inertia\Inertia;
use Praxis\Core\Gamification\RewardCatalog;

class HomeController extends Controller
{
    public function index(RewardCatalog $rewards)
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Admins et professionnels : tableau de bord d'administration.
            if ($user->hasRole('admin') || $user->hasRole('professional')) {
                return redirect()->route('admin.dashboard');
            }

            // Candidats : liste des tests si profil complet, sinon onboarding.
            return $user->profile?->isComplete()
                ? redirect()->route('tests.index')
                : redirect()->route('onboarding.show');
        }

        // Compteurs calculés depuis les données réelles pour que la landing
        // suive l'ajout d'un test ou d'une mini-app. Même périmètre que les
        // pages candidat : les épreuves = l'Armurerie (les tests-cadeaux vivent
        // dans le Trésor, cf. Candidate\TestController), les modules = la
        // Salle du Trésor.
        $rewardSlugs = $rewards->testSlugs();

        return Inertia::render('Public/Landing', [
            'testsCount' => Test::where('published', true)
                ->when($rewardSlugs !== [], fn ($q) => $q->whereNotIn('slug', $rewardSlugs))
                ->count(),
            'miniAppsCount' => $rewards->all()->count(),
        ]);
    }
}
