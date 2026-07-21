<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
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
            'badges'           => $this->badgesForUser($user),
        ]);
    }

    /**
     * Distinctions du candidat : tous les badges, obtenus ou non.
     *
     * Un badge `hidden` non obtenu est renvoyé masqué — son intitulé révélerait
     * l'easter egg correspondant. Il reste compté dans le total : savoir qu'il
     * existe des secrets fait partie du jeu, savoir lesquels non.
     */
    private function badgesForUser(User $user): array
    {
        $corporate = Parcours::isCorporate($user);

        // Map badge_id => earned_at, construite explicitement : un pluck
        // qualifié sur une relation belongsToMany dépend du strip de table,
        // autant ne pas en dépendre.
        $earnedAt = [];
        foreach ($user->badges as $owned) {
            $earnedAt[$owned->id] = $owned->pivot->earned_at ?? null;
        }

        $items = Badge::orderBy('id')->get()->map(function (Badge $badge) use ($corporate, $earnedAt) {
            $earned = array_key_exists($badge->id, $earnedAt);

            if ($badge->hidden && ! $earned) {
                return [
                    'slug'        => "secret-{$badge->id}",
                    'name'        => '???',
                    'description' => $corporate
                        ? 'Une distinction non documentée. Elle se découvre en explorant.'
                        : 'Un secret dort encore quelque part. À toi de le trouver.',
                    'icon'      => 'lock',
                    'earned'    => false,
                    'earned_at' => null,
                    'secret'    => true,
                ];
            }

            return [
                'slug'        => $badge->slug,
                'name'        => $badge->displayName($corporate),
                'description' => $badge->displayDescription($corporate),
                'icon'        => $badge->icon ?: 'award',
                'earned'      => $earned,
                'earned_at'   => $earned && $earnedAt[$badge->id]
                    ? \Illuminate\Support\Carbon::parse($earnedAt[$badge->id])->format('d/m/Y')
                    : null,
                'secret'      => false,
            ];
        })->values();

        return [
            'items'         => $items->all(),
            'earned_count'  => $items->where('earned', true)->count(),
            'total_count'   => $items->count(),
        ];
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
