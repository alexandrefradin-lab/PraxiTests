<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\JourneyProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    // ─── POST /journey/complete ───────────────────────────────────────────────

    public function completeDay(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plugin_slug'     => ['required', 'string', 'in:praxizen,praxiself,praxispeak,praxiflow,praxilink'],
            'day'             => ['required', 'integer', 'min:1', 'max:60'],
            'felt_score'      => ['nullable', 'integer', 'min:1', 'max:5'],
            'duration_actual' => ['nullable', 'integer'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $userId = auth()->id();
        $slug   = $data['plugin_slug'];
        $day    = (int) $data['day'];

        // Vérifier que le jour n'est pas déjà complété
        $exists = JourneyProgress::where('user_id', $userId)
            ->where('plugin_slug', $slug)
            ->where('day', $day)
            ->whereNotNull('completed_at')
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ce jour a déjà été complété.',
            ], 409);
        }

        $extra = array_filter([
            'felt_score'      => $data['felt_score'] ?? null,
            'duration_actual' => $data['duration_actual'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ], fn ($v) => $v !== null);

        JourneyProgress::markComplete($userId, $slug, $day, $extra);

        return response()->json([
            'success'         => true,
            'day'             => $day,
            'streak'          => JourneyProgress::streakFor($userId, $slug),
            'next_day'        => JourneyProgress::currentDay($userId, $slug),
            'completion_rate' => JourneyProgress::completionRate($userId, $slug),
        ]);
    }

    // ─── GET /journey/today?plugin_slug=praxizen ──────────────────────────────

    public function todayData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plugin_slug' => ['required', 'string', 'in:praxizen,praxiself,praxispeak,praxiflow,praxilink'],
        ]);

        $userId = auth()->id();
        $slug   = $data['plugin_slug'];

        $currentDay     = JourneyProgress::currentDay($userId, $slug);
        $streak         = JourneyProgress::streakFor($userId, $slug);
        $completionRate = JourneyProgress::completionRate($userId, $slug);
        $completedDays  = JourneyProgress::completedDays($userId, $slug);

        // Résoudre l'entrée du jour via la classe Journey du plugin
        $journeyClass = $this->resolveJourney($slug);
        $todayEntry   = null;

        if ($journeyClass && class_exists($journeyClass)) {
            $todayEntry = $journeyClass::day($currentDay);
        }

        return response()->json([
            'current_day'     => $currentDay,
            'streak'          => $streak,
            'completion_rate' => $completionRate,
            'completed_days'  => $completedDays,
            'today_entry'     => $todayEntry,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolveJourney(string $slug): ?string
    {
        return match($slug) {
            'praxizen'   => \Praxis\Plugins\PraxiZen\Data\Journey::class,
            'praxiself'  => \Praxis\Plugins\PraxiSelf\Data\Journey::class,
            'praxispeak' => \Praxis\Plugins\PraxiSpeak\Data\Journey::class,
            'praxiflow'  => \Praxis\Plugins\PraxiFlow\Data\Journey::class,
            'praxilink'  => \Praxis\Plugins\PraxiLink\Data\Journey::class,
            default      => null,
        };
    }
}
