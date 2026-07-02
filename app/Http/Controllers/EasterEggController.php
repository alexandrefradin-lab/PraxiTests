<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Praxis\Core\Gamification\BadgeEvaluator;
use Praxis\Core\Gamification\GamificationEngine;

class EasterEggController extends Controller
{
    public function claim(Request $request, GamificationEngine $engine, BadgeEvaluator $evaluator): JsonResponse
    {
        $user = $request->user();

        // Anti-replay : un seul claim par utilisateur
        if ($user->easter_egg_claimed_at !== null) {
            return response()->json(['already_claimed' => true]);
        }

        // Marquer comme claimé
        $user->update(['easter_egg_claimed_at' => now()]);

        // +42 Éclats (idempotency key = "easter_egg:{user_id}")
        $engine->awardXp(
            $user,
            42,
            'easter_egg',
            null,
            [],
            false, // pas d'éval badges ici, on le fait manuellement
            "easter_egg:{$user->id}",
        );

        // Badge "Éveillé"
        $badge = Badge::where('slug', 'eveille')->first();
        if ($badge && ! $user->badges()->where('badges.id', $badge->id)->exists()) {
            $evaluator->award($user, $badge, ['source' => 'konami']);
        }

        return response()->json(['success' => true, 'eclats' => 42]);
    }
}
