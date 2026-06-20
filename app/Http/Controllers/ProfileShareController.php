<?php

namespace App\Http\Controllers;

use App\Models\ProfileShare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileShareController extends Controller
{
    /**
     * Génère (ou régénère) un lien de partage pour l'utilisateur connecté.
     * POST /profile/share
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Vérifie qu'au moins un attempt complété avec résultat existe
        $hasResult = $user->attempts()
            ->where('status', 'completed')
            ->whereHas('result')
            ->exists();

        if (! $hasResult) {
            return response()->json([
                'message' => 'Aucun résultat disponible à partager.',
            ], 422);
        }

        $share = ProfileShare::generateFor($user, daysValid: 30);

        return response()->json([
            'share_url'  => $share->share_url,
            'expires_at' => $share->expires_at->toDateString(),
        ]);
    }

    /**
     * Page publique — accessible sans authentification.
     * GET /p/{token}
     */
    public function show(string $token): Response
    {
        $share = ProfileShare::with(['user.profile', 'user.profileGrimoire', 'user.attempts.result'])
                             ->valid()
                             ->where('token', $token)
                             ->firstOrFail();

        $share->incrementView();

        $user    = $share->user;
        $profile = $user->profile;

        // Dernier attempt complété avec résultat généré (repli si pas de Grimoire global)
        $result = $user->attempts()
            ->where('status', 'completed')
            ->whereHas('result')
            ->with('result')
            ->latest('completed_at')
            ->first()
            ?->result;

        // Priorité à la relecture globale (Le Grimoire) si elle est prête, sinon
        // on retombe sur la dernière tentative.
        $grimoire = $user->profileGrimoire;
        $useGrimoire = $grimoire && $grimoire->isReady();

        return Inertia::render('Profile/SharedView', [
            'profile' => [
                'name'         => $user->name,
                'status'       => $profile?->status,          // employee / entrepreneur / jobseeker …
                'is_grimoire'  => $useGrimoire,
                'synthesis'    => $useGrimoire ? $grimoire->synthesis  : $result?->ai_synthesis,
                'careers'      => $useGrimoire ? ($grimoire->voies ?? []) : ($result?->suggested_jobs ?? []),
                'scores'       => $useGrimoire ? [] : ($result?->scoring ?? []),
                'completed_at' => $useGrimoire
                    ? $grimoire->generated_at?->toDateString()
                    : $result?->created_at?->toDateString(),
            ],
            'expires_at' => $share->expires_at->toDateString(),
        ]);
    }

    /**
     * Révoque le lien actif de l'utilisateur.
     * DELETE /profile/share
     */
    public function destroy(Request $request): JsonResponse
    {
        ProfileShare::where('user_id', $request->user()->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

        return response()->json(['message' => 'Lien révoqué.']);
    }
}
