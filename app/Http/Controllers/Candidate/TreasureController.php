<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Praxis\Core\Gamification\RewardCatalog;

/**
 * La Salle du Trésor — les apps/modules offerts en récompense,
 * déblocables par paliers d'Éclats cumulés.
 */
class TreasureController extends Controller
{
    public function __construct(protected RewardCatalog $rewards) {}

    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Candidate/TreasureRoom', [
            'treasure'         => $this->rewards->forUser($user),
            'profile_complete' => $user->profile?->isComplete() ?? false,
        ]);
    }
}
