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
        $share = ProfileShare::with(['user.profile', 'user.attempts.result'])
                             ->valid()
                             ->where('token', $token)
                             ->firstOrFail();

        $share->incrementView();

        $user    = $share->user;
        $profile = $user->profile;

        // Dernier attempt complété avec résultat généré
        $result = $user->attempts()
            ->where('status', 'completed')
            ->whereHas('result')
            ->with('result')
            ->latest('completed_at')
            ->first()
            ?->result;

        return Inertia::render('Profile/SharedView', [
            'profile' => [
                'name'         => $user->name,
                'status'       => $profile?->status,          // employee / entrepreneur / jobseeker …
                'synthesis'    => $result?->ai_synthesis,
                'careers'      => $result?->suggested_jobs ?? [],
                'scores'       => $result?->scoring ?? [],
                'completed_at' => $result?->created_at?->toDateString(),
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
