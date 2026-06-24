<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Questionnaire de questionnement sur les croyances bloquantes.
 *
 * Affiché quand un utilisateur clique sur le lien dans l'email de relance.
 * 5 étapes de réflexion pour identifier ce qui bloque, puis redirection
 * vers l'action du jour du parcours concerné.
 */
class BlockingBeliefsController extends Controller
{
    private const ALLOWED_PLUGINS = ['praxilead', 'praxizenith'];

    private const PLUGIN_META = [
        'praxilead'  => ['label' => 'Le Cap', 'route' => 'praxilead.show'],
        'praxizenith'=> ['label' => 'Le Sanctuaire de l\'Attention', 'route' => 'praxizenith.show'],
    ];

    /**
     * Affiche le questionnaire pour le plugin + jour donnés.
     */
    public function show(Request $request): Response
    {
        $plugin = $request->query('plugin', 'praxilead');
        $day    = (int) $request->query('day', 1);

        if (! in_array($plugin, self::ALLOWED_PLUGINS, true)) {
            $plugin = 'praxilead';
        }

        $meta = self::PLUGIN_META[$plugin];

        return Inertia::render('BlockingBeliefs', [
            'plugin'      => $plugin,
            'day'         => $day,
            'pluginLabel' => $meta['label'],
            'actionUrl'   => route($meta['route'], $day),
        ]);
    }

    /**
     * Sauvegarde les réponses et redirige vers l'action du jour.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plugin'           => ['required', 'in:' . implode(',', self::ALLOWED_PLUGINS)],
            'day'              => ['required', 'integer', 'min:1', 'max:60'],
            'q1_obstacle'      => ['nullable', 'string', 'max:2000'],
            'q2_category'      => ['nullable', 'string', 'in:peur,fatigue,temps,croyance,autre'],
            'q3_score'         => ['nullable', 'integer', 'min:0', 'max:10'],
            'q4_friend_advice' => ['nullable', 'string', 'max:2000'],
            'q5_small_step'    => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('journey_nudge_responses')->insert([
            'user_id'          => $request->user()->id,
            'plugin'           => $validated['plugin'],
            'day'              => $validated['day'],
            'q1_obstacle'      => $validated['q1_obstacle'] ?? null,
            'q2_category'      => $validated['q2_category'] ?? null,
            'q3_score'         => $validated['q3_score'] ?? null,
            'q4_friend_advice' => $validated['q4_friend_advice'] ?? null,
            'q5_small_step'    => $validated['q5_small_step'] ?? null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $route = self::PLUGIN_META[$validated['plugin']]['route'];

        return redirect()->route($route, $validated['day'])
            ->with('flash', [
                'type'    => 'success',
                'message' => 'Merci pour cette réflexion. L\'action du jour t\'attend 🌱',
            ]);
    }
}
