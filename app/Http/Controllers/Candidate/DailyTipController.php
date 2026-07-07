<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Praxis\Core\DailyTip\DailyTipService;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Core\Library\ExerciseLibrary;

/**
 * Interactions avec le « Tip du jour » d'une mini-app (Salle du Trésor).
 *
 * Le tip lui-même est calculé côté serveur (jamais fourni par le client) pour
 * empêcher de réclamer des Éclats sur un tip arbitraire. Le plugin est validé
 * contre le registre, et l'app doit être débloquée (gating RewardCatalog).
 */
class DailyTipController extends Controller
{
    public function __construct(
        protected DailyTipService $tips,
        protected ExerciseLibrary $library,
        protected RewardCatalog $rewards,
    ) {}

    /** Marque le tip du jour comme vu (sans récompense). */
    public function seen(Request $request, string $plugin)
    {
        $this->guard($request, $plugin);

        if ($tip = $this->tips->todayFor($request->user(), $plugin)) {
            $this->tips->markSeen($request->user(), $plugin, $tip['id']);
        }

        return back();
    }

    /** Marque le tip du jour comme appliqué → série + Éclats (idempotent/jour). */
    public function apply(Request $request, string $plugin)
    {
        $this->guard($request, $plugin);

        $tip = $this->tips->todayFor($request->user(), $plugin);
        abort_if($tip === null, 404, "Aucun tip disponible pour cette app.");

        $result = $this->tips->markApplied($request->user(), $plugin, $tip['id']);

        $msg = $result['eclats_gained'] > 0
            ? "Bravo ! +{$result['eclats_gained']} " . \App\Support\Parcours::xpName() . " · série de {$result['engagement']['streak']} jour(s)."
            : (\App\Support\Parcours::isCorporate() ? "Déjà validé aujourd'hui — revenez demain pour un nouveau conseil." : "Déjà validé aujourd'hui — reviens demain pour un nouveau tip.");

        return back()->with('success', $msg);
    }

    /** Valide le plugin et le déblocage de l'app. */
    protected function guard(Request $request, string $plugin): void
    {
        abort_unless(in_array($plugin, $this->library->tipApps(), true), 404);
        abort_unless(
            $this->rewards->isRouteUnlocked("{$plugin}.index", $request->user()),
            403,
            (\App\Support\Parcours::isCorporate() ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé."),
        );
    }
}
