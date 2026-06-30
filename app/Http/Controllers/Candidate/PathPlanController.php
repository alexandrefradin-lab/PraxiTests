<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\CareerPath;
use App\Models\CareerPathPlan;
use Illuminate\Http\JsonResponse;
use Praxis\Core\Orientation\PathPlanService;

/**
 * Génère et retourne le plan d'action IA pour une piste métier + profil candidat.
 *
 * Route : POST /career-path/{careerPath}/plan   (throttle:5,1 — 5 générations/min)
 *         GET  /career-path/{careerPath}/plan   (lecture du plan existant sans génération)
 *
 * Réponse JSON :
 *   { plan: { premier_pas, etapes[], ressources[], conseil }, cached: bool }
 */
class PathPlanController extends Controller
{
    public function __construct(protected PathPlanService $service) {}

    /**
     * Retourne le plan existant (si déjà généré) sans appel IA.
     */
    public function show(CareerPath $careerPath): JsonResponse
    {
        $user    = auth()->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['plan' => null, 'cached' => false]);
        }

        $existing = CareerPathPlan::where('profile_id', $profile->id)
            ->where('career_path_id', $careerPath->id)
            ->first();

        return response()->json([
            'plan'   => $existing?->plan_json ?? null,
            'cached' => $existing && !empty($existing->plan_json),
        ]);
    }

    /**
     * Génère le plan d'action (ou retourne le cache existant).
     * Throttle : 5 générations / minute / utilisateur (coût IA).
     */
    public function generate(CareerPath $careerPath): JsonResponse
    {
        $user    = auth()->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'error' => 'Profil introuvable. Complète ton profil avant de générer un plan.',
            ], 422);
        }

        if (!$careerPath->active) {
            return response()->json(['error' => 'Cette piste n\'est pas disponible.'], 404);
        }

        $plan = $this->service->getOrGenerate($profile, $careerPath);

        if (empty($plan)) {
            return response()->json([
                'error' => 'La génération a échoué. Réessaie dans quelques instants.',
            ], 503);
        }

        $cached = CareerPathPlan::where('profile_id', $profile->id)
            ->where('career_path_id', $careerPath->id)
            ->whereNotNull('generated_at')
            ->exists();

        return response()->json([
            'plan'   => $plan,
            'cached' => $cached,
        ]);
    }
}
