<?php

namespace Praxis\Core\Mailing\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Praxis\Core\Mailing\Mail\CampaignMail;
use Praxis\Core\Mailing\NeuromarketingOptimizer;

/**
 * Exécute une étape d'une séquence pour un utilisateur donné.
 * Les délais entre étapes sont planifiés via la queue (delay($hours)).
 */
class SequenceRunner
{
    public function __construct(protected NeuromarketingOptimizer $neuro) {}

    public function trigger(string $event, User $user, array $context = []): void
    {
        $sequences = DB::table('email_sequences')
            ->where('trigger_event', $event)
            ->where('enabled', true)
            ->get();

        foreach ($sequences as $sequence) {
            $this->scheduleSteps($sequence, $user, $context);
        }
    }

    protected function scheduleSteps(object $sequence, User $user, array $context): void
    {
        $steps = json_decode($sequence->steps, true) ?: [];
        $delay = 0;
        foreach ($steps as $i => $step) {
            $delay += (int) ($step['delay_hours'] ?? 0);
            \App\Jobs\SendSequenceStepJob::dispatch($sequence->id, $user->id, $i, $context)
                ->delay(now()->addHours($delay));
        }
    }

    public function runStep(int $sequenceId, int $userId, int $stepIndex, array $context = []): void
    {
        $sequence = DB::table('email_sequences')->find($sequenceId);
        $user = User::find($userId);
        if (!$sequence || !$user) return;

        $steps = json_decode($sequence->steps, true) ?: [];
        $step = $steps[$stepIndex] ?? null;
        if (!$step) return;

        if (!$this->matchesConditions($user, $step['conditions'] ?? [])) {
            return;
        }

        $html = $this->neuro->enhanceHtml($step['body_html'] ?? '', array_merge($context, [
            'user' => $user,
            'progress_percent' => $step['progress_percent'] ?? null,
        ]));

        Mail::to($user->email)->queue(new CampaignMail(
            $step['subject'] ?? '(sans sujet)',
            $html,
            $step['body_text'] ?? null,
        ));

        DB::table('email_logs')->insert([
            'user_id'    => $user->id,
            'sequence_id' => $sequence->id,
            'step'       => $stepIndex + 1,
            'to_email'   => $user->email,
            'subject'    => $step['subject'] ?? '',
            'status'     => 'queued',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function matchesConditions(User $user, array $conditions): bool
    {
        // exemple : { "min_xp": 100, "has_completed_test": true }
        if (isset($conditions['min_xp']) && $user->totalXp() < (int) $conditions['min_xp']) return false;
        if (!empty($conditions['has_completed_test'])
            && !$user->attempts()->where('status', 'completed')->exists()) return false;
        return true;
    }
}
