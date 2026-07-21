<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Parcours;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\MiniAppUnlockException;
use Praxis\Core\Gamification\MiniAppUnlockService;
use Praxis\Core\Gamification\RewardCatalog;

/**
 * La Salle du Trésor — les mini-apps offertes en récompense.
 *
 * La salle s'ouvre une fois TOUTES les Épreuves passées ; le candidat choisit
 * ensuite librement quelle mini-app ouvrir, en dépensant ses Éclats.
 */
class TreasureController extends Controller
{
    public function __construct(
        protected RewardCatalog $rewards,
        protected MiniAppUnlockService $unlocks,
    ) {}

    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Candidate/TreasureRoom', [
            'treasure'         => $this->rewards->forUser($user),
            'profile_complete' => $user->profile?->isComplete() ?? false,
        ]);
    }

    /** Ouverture d'une mini-app choisie par le candidat. */
    public function unlock(Request $request, string $slug)
    {
        $user = $request->user();

        try {
            $result = $this->unlocks->unlock($user, $slug);
        } catch (MiniAppUnlockException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result['already']) {
            return back();
        }

        return back()->with(
            'success',
            Parcours::unlockedMessage($result['reward']['name'], $result['balance'], $user)
        );
    }
}
