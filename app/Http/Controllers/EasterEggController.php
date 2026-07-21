<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\UserEasterEgg;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Praxis\Core\Gamification\BadgeEvaluator;
use Praxis\Core\Gamification\EasterEggRegistry;
use Praxis\Core\Gamification\GamificationEngine;

class EasterEggController extends Controller
{
    /**
     * « Le Faux Bouton » — destination du lien discret de la page 404.
     * Volontairement publique : on peut se perdre sans être connecté. Le
     * claim, lui, reste derrière l'auth (la page ne l'appelle pas pour un
     * visiteur anonyme).
     */
    public function nullePart(Request $request): Response
    {
        return Inertia::render('Public/NullePart', [
            'can_claim' => $request->user() !== null,
        ]);
    }

    public function claim(Request $request, GamificationEngine $engine, BadgeEvaluator $evaluator): JsonResponse
    {
        // Le client n'envoie qu'un slug : les Éclats et le badge viennent du
        // registre serveur, jamais de la requête.
        $validated = $request->validate([
            'slug' => ['required', 'string', Rule::in(EasterEggRegistry::slugs())],
        ]);

        $slug = $validated['slug'];
        $egg  = EasterEggRegistry::get($slug);
        $user = $request->user();

        // Anti-replay porté par l'index unique (user_id, slug) : deux requêtes
        // concurrentes ne peuvent pas créditer deux fois.
        try {
            UserEasterEgg::create([
                'user_id'    => $user->id,
                'slug'       => $slug,
                'claimed_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['already_claimed' => true]);
        }

        // Clé d'idempotence historique pour le Konami : elle protège les
        // comptes crédités avant la refonte multi-eggs, dont la ligne
        // user_easter_eggs a été reconstituée par backfill.
        $idempotencyKey = $slug === 'konami'
            ? "easter_egg:{$user->id}"
            : "easter_egg:{$slug}:{$user->id}";

        $engine->awardXp(
            $user,
            $egg['eclats'],
            'easter_egg',
            null,
            ['egg' => $slug],
            false, // pas d'éval badges ici, on le fait manuellement juste après
            $idempotencyKey,
        );

        $badge = Badge::where('slug', $egg['badge'])->first();
        if ($badge && ! $user->badges()->where('badges.id', $badge->id)->exists()) {
            $evaluator->award($user, $badge, ['source' => $slug]);
        }

        return response()->json([
            'success'    => true,
            'eclats'     => $egg['eclats'],
            'badge_name' => $badge?->name,
        ]);
    }
}
