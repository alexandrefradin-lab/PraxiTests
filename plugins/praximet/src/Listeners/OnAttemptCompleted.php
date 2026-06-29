<?php

namespace Praxis\Plugins\PraxiMet\Listeners;

use App\Models\Lead;
use App\Models\TestAttempt;

class OnAttemptCompleted
{
    public static function handle(TestAttempt $attempt): void
    {
        if ($attempt->test->slug !== 'praximet-riasec' || !$attempt->user) {
            return;
        }

        // Crée / upgrade un lead PraxiMet.
        Lead::updateOrCreate(
            ['email' => $attempt->user->email],
            [
                'user_id'    => $attempt->user->id,
                'first_name' => $attempt->user->name,
                'source'     => 'praximet-riasec',
                'score'      => static::leadScore($attempt),
                'status'     => 'qualified',
                'metadata'   => [
                    'riasec_code' => $attempt->result?->scoring['code'] ?? null,
                    'attempt_id'  => $attempt->id,
                ],
                'last_activity_at' => now(),
            ]
        );
    }

    /** Score lead basé sur cohérence du profil RIASEC (différentiel des 3 premiers types). */
    protected static function leadScore(TestAttempt $attempt): int
    {
        $raw = $attempt->result?->scoring['raw_scores'] ?? [];
        if (count($raw) < 3) return 50;
        rsort($raw);
        $diff = ($raw[0] ?? 0) - ($raw[2] ?? 0);
        return min(100, 60 + $diff * 3);
    }
}
