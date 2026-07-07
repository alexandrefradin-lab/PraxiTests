<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\JourneyProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Journey\JourneyEngine;
use Praxis\Core\Journey\JourneyRegistry;

/**
 * Tableau de bord de parcours 60 jours — mutualisé pour tous les plugins
 * enregistrés dans JourneyRegistry. Rend les pages génériques
 * Candidate/JourneyIndex (dashboard) et Candidate/JourneyPractice (jour).
 */
class JourneyDashboardController extends Controller
{
    public function __construct(
        protected JourneyEngine $engine,
        protected GamificationEngine $gamification,
    ) {}

    public function index(Request $request, string $plugin)
    {
        abort_unless(JourneyRegistry::has($plugin), 404);

        $config = JourneyRegistry::get($plugin);
        $userId = $request->user()->id;

        $current   = $this->engine->currentDay($userId, $plugin);
        $completed = JourneyProgress::completedDays($userId, $plugin);

        $practices = collect(JourneyRegistry::days($plugin))->map(function ($d) use ($userId, $plugin, $current, $completed) {
            $day = (int) ($d['day'] ?? 0);

            return [
                'day'          => $day,
                'theme'        => $d['theme'] ?? '',
                'title'        => $d['title'] ?? '',
                'summary'      => $d['summary'] ?? '',
                'duration_min' => $d['duration_min'] ?? null,
                'icon'         => $d['icon'] ?? 'sparkles',
                'unlocked'     => $this->engine->isUnlocked($userId, $plugin, $day),
                'completed'    => isset($completed[$day]),
                'is_today'     => $day === $current,
                'days_left'    => $this->engine->daysUntilUnlock($userId, $plugin, $day),
                'eclats'       => JourneyEngine::ECLATS_PER_PRACTICE,
            ];
        })->values();

        return Inertia::render('Candidate/JourneyIndex', [
            'meta' => [
                'slug'     => $plugin,
                'title'    => $config['title'] ?? '',
                'subtitle' => $config['subtitle'] ?? '',
                'color'    => $config['color'] ?? '#B87A1A',
            ],
            'practices'  => $practices,
            'currentDay' => $current,
            'totalDays'  => JourneyEngine::TOTAL_DAYS,
            'completed'  => count($completed),
            'streak'     => JourneyProgress::streakFor($userId, $plugin),
        ]);
    }

    public function show(Request $request, string $plugin, int $day)
    {
        abort_unless(JourneyRegistry::has($plugin), 404);

        $config = JourneyRegistry::get($plugin);
        $userId = $request->user()->id;

        abort_unless(
            $this->engine->isUnlocked($userId, $plugin, $day),
            403,
            'Cette pratique se débloquera dans ' . $this->engine->daysUntilUnlock($userId, $plugin, $day) . ' jour(s).'
        );

        $entry = JourneyRegistry::day($plugin, $day);
        abort_if($entry === null, 404);

        $pr = JourneyProgress::where('user_id', $userId)
            ->where('plugin_slug', $plugin)
            ->where('day', $day)
            ->first();

        return Inertia::render('Candidate/JourneyPractice', [
            'meta' => [
                'slug'  => $plugin,
                'title' => $config['title'] ?? '',
                'color' => $config['color'] ?? '#B87A1A',
            ],
            'practice' => [
                'day'             => (int) $entry['day'],
                'theme'           => $entry['theme'] ?? '',
                'title'           => $entry['title'] ?? '',
                'summary'         => $entry['summary'] ?? '',
                'body'            => $entry['body'] ?? '',
                'micro_challenge' => $entry['micro_challenge'] ?? '',
                'duration_min'    => $entry['duration_min'] ?? null,
                'icon'            => $entry['icon'] ?? 'sparkles',
            ],
            'state' => [
                'completed'  => $pr?->completed_at !== null,
                'felt_score' => $pr?->felt_score,
                'notes'      => $pr?->notes,
            ],
            'nav' => [
                'prev' => $day > 1 ? $day - 1 : null,
                'next' => ($day < JourneyEngine::TOTAL_DAYS && $this->engine->isUnlocked($userId, $plugin, $day + 1))
                    ? $day + 1
                    : null,
            ],
            'eclatsPerPractice' => JourneyEngine::ECLATS_PER_PRACTICE,
        ]);
    }

    public function complete(Request $request, string $plugin, int $day)
    {
        abort_unless(JourneyRegistry::has($plugin), 404);
        $userId = $request->user()->id;

        abort_unless($this->engine->isUnlocked($userId, $plugin, $day), 403);

        $data = $request->validate([
            'felt_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $already = JourneyProgress::where('user_id', $userId)
            ->where('plugin_slug', $plugin)
            ->where('day', $day)
            ->whereNotNull('completed_at')
            ->exists();

        JourneyProgress::markComplete($userId, $plugin, $day, array_filter([
            'felt_score' => $data['felt_score'] ?? null,
            'notes'      => $data['notes'] ?? null,
        ], fn ($v) => $v !== null));

        if (! $already) {
            $this->gamification->awardXp(
                $request->user(),
                JourneyEngine::ECLATS_PER_PRACTICE,
                'journey.practice_done',
                null,
                ['plugin' => $plugin, 'day' => $day],
                false,
            );
        }

        return back()->with(
            'success',
            $already
                ? 'Ressenti mis à jour.'
                : 'Pratique intégrée ! +' . JourneyEngine::ECLATS_PER_PRACTICE . ' ' . \App\Support\Parcours::xpName() . '.'
        );
    }
}
